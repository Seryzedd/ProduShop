<?php

namespace App\Controller\Cart\Payment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Payment\StripeCardType;
use App\Service\Api\StripeService;
use App\Service\Payment\StripeCustomerService;
use App\Service\Cart\CartService;
use App\Service\Order\OrderService;

#[Route('/cart/payment')]
final class PaymentController extends AbstractController
{
    public function __construct(
        private CartService $cart,
        private StripeCustomerService $stripeCustomerService,
        private StripeService $stripeService,
        private OrderService $orderService,
    ) {}

    #[Route('/', name: 'app_cart_payment')]
    public function index(Request $request): Response
    {
        if($this->cart->isEmpty()) {
            $this->addFlash('info', 'No product in cart to pay. Try to add some first.');
        }

        if(!$this->stripeService->isReady()) {
            $this->addFlash('info', 'Payments configuration are inactive.');

            return $this->redirectToRoute('app_cart');
        }

        /** @var \App\Entity\User\Client $client */
        $client = $this->getUser();

        $paymentMethods = $this->stripeCustomerService->getPaymentMethods($client);

        $form = $this->createForm(StripeCardType::class, null, [
            'payment_methods' => $paymentMethods,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedId      = $form->get('paymentMethodId')->getData();
            $newMethodId     = $form->get('newPaymentMethodId')->getData();
            $paymentMethodId = $selectedId === 'new' ? $newMethodId : $selectedId;

            if (empty($paymentMethodId)) {
                $this->addFlash('danger', 'Payment method required.');
                return $this->redirectToRoute('app_cart_payment');
            }

            try {
                // One PaymentIntent per Professional — failed merchants are skipped
                $result = $this->stripeCustomerService->createPaymentIntentsFromCart(
                    client:          $client,
                    currency:        'eur',
                    paymentMethodId: $paymentMethodId
                );

                dump($result);

                if (empty($result['succeeded'])) {
                    throw new \RuntimeException('All payment intents failed. No charge was made.');
                }

                $confirmed = [];
                foreach ($result['succeeded'] as $entry) {
                    $intent = $entry['intent'];
                    $professional = $entry['professional'];

                    if (in_array($intent['status'], ['requires_confirmation', 'requires_payment_method'])) {
                        $intent = $this->stripeService->confirmPaymentIntent(
                            $intent['id'],
                            $paymentMethodId
                        );
                    }

                    // 3DS required — not supported in server-side only flow
                    if ($intent['status'] === 'requires_action') {
                        throw new \RuntimeException('payment.card_requires_3ds');
                    }

                    $this->orderService->persistFromIntent($client, $professional, $intent, $entry['items']);

                    $confirmed[] = [
                        'intent'   => $intent,
                        'merchant' => $entry['merchant'],
                        'professional' => $professional,
                        'items'    => $entry['items'],
                    ];
                }

                $this->orderService->save();

                $this->savePaymentSummary($request, $confirmed, $result['failed']);

                return $this->redirectToRoute('app_payment_success');

            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('cart/payment/index.html.twig', [
            'form'              => $form,
            'stripe_public_key' => $this->stripeService->getPublicKey(),
            'has_saved_methods' => !empty($paymentMethods),
        ]);
    }

    #[Route('/success', name: 'app_payment_success')]
    public function paymentSuccess(Request $request): Response
    {
        $summary = $request->getSession()->get('payment_summary');

        if ($summary === null) {
            return $this->redirectToRoute('app_home_index');
        }

        $request->getSession()->remove('payment_summary');
        $this->cart->clear();

        return $this->render('cart/payment/success.html.twig', [
            'summary' => $summary,
        ]);
    }

    // ----------------- Private functions -------------------

    /**
     * Stores a unified payment summary in session for the success page.
     *
     * Structure:
     * [
     *   'paid_at'    => '...',
     *   'orders'     => [
     *     ['merchant' => 'acct_xxx', 'items' => [...], 'amount' => 3050, 'currency' => 'eur'],
     *     ...
     *   ],
     *   'total'      => 5050,   // sum of all confirmed intents in cents
     *   'failed'     => [['merchant' => 'CompanyName', 'reason' => '...', 'items' => [...]], ...]
     * ]
     */
    private function savePaymentSummary(Request $request, array $confirmed, array $failed): void
    {
        $orders = array_map(function (array $entry) {
            /** @var \App\Entity\User\Professional $professional */
            $professional = $entry['professional'];
            $adress       = $professional->getAdress();

            return [
                'intent_id' => $entry['intent']['id'],
                'merchant'  => [
                    'account_id'   => $entry['merchant'],
                    'company_name' => $professional->getCompanyName(),
                    'street'       => $adress->getStreet(),
                    'zip_code'     => $adress->getZipCode(),
                    'country'      => $adress->getCountry(),
                    'complement'   => $adress->getComplement(),
                ],
                'items'    => $entry['items'],
                'amount'   => $entry['intent']['amount'],
                'currency' => $entry['intent']['currency'],
            ];
        }, $confirmed);

        $request->getSession()->set('payment_summary', [
            'paid_at' => (new \DateTimeImmutable())->format('d/m/Y H:i'),
            'orders'  => $orders,
            'total'   => array_sum(array_column(array_column($confirmed, 'intent'), 'amount')),
            'failed'  => $failed,
        ]);
    }
}
