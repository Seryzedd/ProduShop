<?php

namespace App\Service\Sales;

use App\Repository\User\Payment\PaymentRepository;

class SalesStatsService
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository,
    ) {
    }

    /**
     * @return array{labels: string[], totals: float[], counts: int[]}
     */
    public function getMonthlySalesStats(int $monthsCount = 12): array
    {
        $payments = $this->paymentRepository->findLastMonthsPayments($monthsCount);

        $months = $this->buildEmptyMonths($monthsCount);

        foreach ($payments as $payment) {
            $key = $payment->getCreatedAt()->format('Y-m');
            if (isset($months[$key])) {
                $months[$key]['total'] += $payment->getAmount();
                $months[$key]['count']++;
            }
        }

        return [
            'labels' => array_column($months, 'label'),
            'totals' => array_map(fn(array $m) => $m['total'] / 100, array_values($months)),
            'counts' => array_column($months, 'count'),
        ];
    }

    private function buildEmptyMonths(int $monthsCount): array
    {
        $months = [];
        $period = new \DatePeriod(
            new \DateTimeImmutable(sprintf('first day of -%d months midnight', $monthsCount - 1)),
            new \DateInterval('P1M'),
            $monthsCount
        );

        foreach ($period as $date) {
            $months[$date->format('Y-m')] = [
                'label' => ucfirst($date->format('M Y')),
                'total' => 0,
                'count' => 0,
            ];
        }

        return $months;
    }
}