<?php

namespace App\Controller;

use App\Entity\Room;
use App\Repository\RoomRepository;
use App\Service\AnnuaireService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/rooms')]
final class RoomController extends AbstractController
{
    private AnnuaireService $annuaire;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private RoomRepository $roomRepository;

    /**
     * @param AnnuaireService $annuaire
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param RoomRepository $roomRepository
     */
    public function __construct(AnnuaireService $annuaire, SerializerInterface $serializer, EntityManagerInterface $em, RoomRepository $roomRepository)
    {
        $this->annuaire = $annuaire;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->roomRepository = $roomRepository;
    }


    #[Route(name: 'get_all_rooms', defaults: ['_api_resource_class' => Room::class],methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->annuaire->getUser($request);
        $objects = $this->roomRepository->findBy(['user' => $user, 'deleted_at' => null], ['name' => 'asc']);

        $json = $this->serializer->serialize($objects, 'json', ['groups' => 'room:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(name: 'add_room', defaults: ['_api_resource_class' => Room::class],methods: ['POST'])]
    public function new(Request $request): Response
    {
        $user = $this->annuaire->getUser($request);

        $object = $this->serializer->deserialize($request->getContent(), Room::class, 'json', ['groups' => 'room:write']);
        $object->setUser($user);

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($object, 'json', ['groups' => 'room:read']), Response::HTTP_CREATED, [], true);
    }

    #[Route(path:'/{id}', name: 'get_room', defaults: ['_api_resource_class' => Room::class], methods: ['GET'])]
    public function show(Request $request, Room $object): Response
    {
        $user = $this->annuaire->getUser($request);
        $object = $this->roomRepository->findOneBy(['id' => $object->getId(), 'user'=>$user, 'deleted_at' => null]);

        if (!$object) {
            return new JsonResponse(['error' => 'Pièce introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $json = $this->serializer->serialize($object, 'json', ['groups' => 'room:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: 'edit_room', defaults: ['_api_resource_class' => Room::class], methods: ['PATCH'])]
    public function edit(Request $request, Room $object): Response
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);
        $object = $this->roomRepository->findOneBy(['id' => $object->getId(), 'user' => $user, 'deleted_at' => null]);

        if (!$object) {
            return new JsonResponse(['error' => 'Pièce introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        if ($content) {
            $object = $this->serializer->deserialize(json_encode($content), Room::class, 'json', ['groups' => 'room:write', 'object_to_populate' => $object]);
        }

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($object, 'json', ['groups' => 'room:read']), Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: 'delete_room', defaults: ['_api_resource_class' => Room::class],methods: ['DELETE'])]
    public function delete(Request $request, Room $object): Response
    {
        $user = $this->annuaire->getUser($request);
        $object = $this->roomRepository->findOneBy(['id' => $object->getId(), 'user' => $user]);

        if (!$object) {
            return new JsonResponse(['error' => 'Pièce introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $object->setDeletedAt(new \DateTimeImmutable());

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse('Pièce supprimée !', Response::HTTP_ACCEPTED);
    }
}
