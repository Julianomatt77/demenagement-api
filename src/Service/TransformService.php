<?php

namespace App\Service;

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
}