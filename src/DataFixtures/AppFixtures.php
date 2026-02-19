<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\PointOfSale;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $posData = [
            ['name' => 'Berlin Flagship Store', 'isActive' => true,  'orders' => 500],
            ['name' => 'Munich Online Shop',    'isActive' => true,  'orders' => 1000],
            ['name' => 'Hamburg Pop-Up',         'isActive' => true,  'orders' => 150],
            ['name' => 'Closed Dresden Store',   'isActive' => false, 'orders' => 200],
        ];

        foreach ($posData as $data) {
            $pos = new PointOfSale();
            $pos->setName($data['name']);
            $pos->setIsActive($data['isActive']);
            $manager->persist($pos);

            for ($i = 0; $i < $data['orders']; ++$i) {
                $order = new Order();
                $order->setPointOfSale($pos);
                // Random amount between 5.00 and 500.00 EUR.
                $order->setTotalAmount(
                    (string) round(mt_rand(500, 50000) / 100, 2)
                );
                // Spread orders across the last year.
                $order->setCreatedAt(
                    new \DateTimeImmutable(sprintf('-%d days', mt_rand(0, 365)))
                );
                $manager->persist($order);
            }
        }

        $manager->flush();
    }
}
