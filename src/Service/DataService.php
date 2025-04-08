<?php

namespace App\Service;

use App\Entity\Carton;
use App\Entity\Demenageur;
use Doctrine\ORM\EntityManagerInterface;

class DataService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setDemenageurPriceLeft(Demenageur $demenageur): Demenageur
    {
        if (!$demenageur->getPaid()) {
            $demenageur->setPaid(0);
        }

        if (!$demenageur->getLeftToPaid()) {
            $demenageur->setLeftToPaid(0);
        }

        if ($demenageur->getDevisPrice() && $demenageur->getDevisPrice() > 0) {
            if ($demenageur->getPaid() <= 0) {
                $demenageur->setLeftToPaid($demenageur->getDevisPrice());
            } else {
                $montant_restant = $demenageur->getDevisPrice() - $demenageur->getPaid();
                if ($montant_restant < 0) {
                    $demenageur->setLeftToPaid(0);
                } else {
                    $demenageur->setLeftToPaid($montant_restant);
                }
            }
        }

        return $demenageur;
    }

    public function isCartonNumberExisting(int $number): bool
    {
        $cartonRepository = $this->em->getRepository(Carton::class);
        $carton = $cartonRepository->findOneBy(['numero' => $number, 'deleted_at' => null]);

        if ($carton) {
            return true;
        }

        return false;
    }

    public function getNextAvailableCartonNumber(): int
    {
        $cartonRepository = $this->em->getRepository(Carton::class);
        $cartons = $cartonRepository->findBy(['deleted_at' => null], ['numero' => 'asc']);

        $nextNumber = 1;

        foreach ($cartons as $carton) {
            if ($carton->getNumero() > $nextNumber) {
                // On a trouvé un trou
                break;
            }
            // Sinon, on continue à chercher
            $nextNumber = $carton->getNumero() + 1;
        }

        return $nextNumber;
    }

    public function checkValidInAndOutBox(array $data): bool
    {
        if (isset($data['in_box']) && $data['in_box'] === true) {
            if (isset($data['out_box']) && $data['out_box'] === true) {
                return false;
            }
        }

        return true;
    }
}