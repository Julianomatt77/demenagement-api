<?php

namespace App\Service;

use App\Entity\Demenageur;

class DataService
{
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
}