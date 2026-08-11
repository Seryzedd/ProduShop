<?php

namespace App\Repository\Product;

use App\Entity\Product\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User\PostalAdress\Adress;
use App\Entity\User\Professional;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Returns products of merchant
     *
     * @return Product[]
     */
    public function findByMerchant(Professional $merchant)
    {
        return $this->createQueryBuilder('p')
            ->join('p.company', 'company')
            ->join('company.stripeAccount', 'stripe')
            ->where('stripe.ready = true')
            ->andWhere('company.stripeAccount = :id')
            ->setParameter('id', $merchant->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.company', 'company')
            ->join('company.stripeAccount', 'stripe')
            ->where('stripe.ready = true')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByShelf(string $shelf): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.stripeAccount', 'stripe')
            ->where('stripe.ready = true')
            ->andWhere('p.shelf = :shelf')
            ->setParameter('shelf', $shelf)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getStats(): array
    {
        $byShelf = $this->createQueryBuilder('p')
            ->select('s.name as shelf, COUNT(p.id) as total, AVG(pkg.price) as avg_price')
            ->join('p.packages', 'pkg')
            ->leftJoin('p.shelf', 's')
            ->groupBy('s.id')
            ->getQuery()
            ->getArrayResult();

        $byShelf = array_map(fn($row) => [
            'shelf'     => $row['shelf'] ?? 'Sans rayon',
            'total'     => (int) $row['total'],
            'avg_price' => round((float) $row['avg_price'], 2),
        ], $byShelf);

        $totalProducts = array_sum(array_column($byShelf, 'total'));
        $globalAvg     = $totalProducts > 0
            ? round(array_sum(array_map(
                fn($r) => $r['avg_price'] * $r['total'],
                $byShelf
            )) / $totalProducts, 2)
            : 0;

        return [
            'total'     => $totalProducts,
            'avg_price' => $globalAvg,
            'by_shelf'  => $byShelf,
        ];
    }

    /**
     * Returns products whose producer is located within $radiusKm
     * kilometres of the logged-in client.
     *
     * Requires that Client->shippingAdress and Professional->adress
     * both have their coordinates (lat/lng) stored in the database.
     *
     * Haversine formula:
     *   d = 2R × arcsin(√( sin²(Δlat/2) + cos(lat1)·cos(lat2)·sin²(Δlng/2) ))
     * where R = 6371 km (mean Earth radius)
     *
     * @return Product[]
     */
    public function findWithinRadius(
        Adress $adress,
        ?float $radiusKm = 20,
        ?string  $shelfName  = null,
    ): array {
        $latitude = $adress->getLatitude();
        $longitude = $adress->getLongitude();

        if(!$latitude || !$longitude) {
            throw new \Exception('Error ! No coordinates found.');
        }

        return $this->findBycoordinates($longitude, $latitude, $radiusKm, $shelfName)->getResult();
    }

    public function findWithinRadiusToArray(
        Adress $adress,
        ?float $radiusKm = 20,
        ?string  $shelfName  = null,
    ): array {
        $latitude = $adress->getLatitude();
        $longitude = $adress->getLongitude();

        return $this->findBycoordinates($longitude, $latitude, $radiusKm, $shelfName)->getArrayResult();
    }

    public function findByNumbers(float $lng, float $lat, ?float $radius = 20, ?string $shelfName = null)
    {
        return $this->findBycoordinates($lng, $lat, $radius, $shelfName)->getResult();
    }

    public function findByNumbersToArray(float $lng, float $lat, ?float $radius = 20, ?string $shelfName = null)
    {
        return $this->findBycoordinates($lng, $lat, $radius, $shelfName)->getArrayResult();
    }

    private function findBycoordinates(float $lng, float $lat, ?float $radius = 20, ?string $shelfName = null): Query
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('company', 'addr', 'shelf', 'image')
            ->join('p.company', 'company')
            ->join('company.adress', 'addr')
            ->join('company.stripeAccount', 'stripe')
            ->leftJoin('p.shelf',    'shelf')
            ->leftJoin('p.image',    'image')
            // Only consider addresses with stored coordinates
            ->where('addr.latitude  IS NOT NULL')
            ->andWhere('addr.longitude IS NOT NULL')
            // Haversine distance filter — all math functions are native DQL
            ->andWhere('
                (6371 * 2 * ASIN(SQRT(
                    POWER(SIN(RADIANS(addr.latitude  - :lat) / 2), 2)
                    + COS(RADIANS(:lat)) * COS(RADIANS(addr.latitude))
                    * POWER(SIN(RADIANS(addr.longitude - :lng) / 2), 2)
                ))) <= :radius
            ')
            // Filter completed Stripe's account
            ->andWhere('stripe.ready = true')
            ->setParameter('lat',    $lat)
            ->setParameter('lng',    $lng)
            ->setParameter('radius', $radius);

        // Optional shelf name filter — case-insensitive partial match
        if ($shelfName !== null) {
            $qb->andWhere('LOWER(shelf.name) LIKE LOWER(:shelfName)')
               ->setParameter('shelfName', '%' . $shelfName . '%');
        }

        return $qb->getQuery();
    }

    /**
     * Fallback: returns all products with their relations (no distance filter).
     *
     * @return Product[]
     */
    public function findAllWithCompanyAndAddress(): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('company', 'addr', 'shelf', 'image')
            ->leftJoin('p.company', 'company')
            ->leftJoin('company.adress', 'addr')
            ->leftJoin('p.shelf', 'shelf')
            ->leftJoin('p.image', 'image')
            ->where('company.adress IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
