<?php

namespace App\Service;

use App\Entity\Carton;
use App\Entity\Room;
use Doctrine\ORM\EntityManagerInterface;

class TransformService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getRoom(array $data): Room | null
    {
        $roomRepository = $this->em->getRepository(Room::class);

        if (isset($data['room'])) {
            $room = $roomRepository->findOneBy(['id' =>$data['room'], 'deleted_at' => null]);

            if ($room) {
                return $room;
            }
        }
        return null;
    }

    public function getcarton(array $data): Carton | null
    {
        $roomRepository = $this->em->getRepository(Carton::class);

        if (isset($data['carton'])) {
            $carton = $roomRepository->findOneBy(['numero' =>$data['carton'], 'deleted_at' => null]);

            if ($carton) {
                return $carton;
            }
        }
        return null;
    }
}