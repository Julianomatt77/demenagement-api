<?php

namespace App\Controller;

use App\Entity\Carton;
use App\Repository\CartonRepository;
use App\Service\AnnuaireService;
use App\Service\DataService;
use App\Service\TransformService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[Route('/api/cartons')]
final class CartonController extends AbstractController
{
    private AnnuaireService $annuaire;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private CartonRepository $cartonRepository;
    private TransformService $transformService;
    private DataService $dataService;

    /**
     * @param AnnuaireService $annuaire
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param CartonRepository $cartonRepository
     * @param TransformService $transformService
     * @param DataService $dataService
     */
    public function __construct(AnnuaireService $annuaire, SerializerInterface $serializer, EntityManagerInterface $em, CartonRepository $cartonRepository, TransformService $transformService, DataService $dataService)
    {
        $this->annuaire = $annuaire;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->cartonRepository = $cartonRepository;
        $this->transformService = $transformService;
        $this->dataService = $dataService;
    }

    #[Route(name: 'get_all_cartons', defaults: ['_api_resource_class' => Carton::class],methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->annuaire->getUser($request);
        $filters = $request->query->all();
//        $objects = $this->cartonRepository->findBy(['user' => $user, 'deleted_at' => null], ['numero' => 'asc']);
        $objects = $this->cartonRepository->findByUserGroupedByRoom($user, $filters);

        $json = $this->serializer->serialize($objects, 'json', ['groups' => 'carton:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(name: 'add_carton', defaults: ['_api_resource_class' => Carton::class],methods: ['POST'])]
    public function new(Request $request): Response
    {
        $user = $this->annuaire->getUser($request);

        $content = json_decode($request->getContent(), true);
        $room = $this->transformService->getRoom($content);

        if (isset($content['room']) && !$room) {
            return new JsonResponse(['error' => 'Pièce introuvable'], Response::HTTP_NOT_FOUND);
        }
        if ($room) {
            unset($content['room']);
        }

        $object = $this->serializer->deserialize(json_encode($content), Carton::class, 'json', ['groups' => 'carton:write']);
        $object->setUser($user);
        if ($room) {
            $object->setRoom($room);
        }

        if (!$object->getNumero()){
            $objects = $this->cartonRepository->findBy(['user' => $user, 'deleted_at' => null], ['id' => 'desc']);
            if (count($objects) > 0) {
                $nextNumber = $this->dataService->getNextAvailableCartonNumber();
                $object->setNumero($nextNumber);
            } else {
                $object->setNumero(1);
            }
        } else {
            if ($this->dataService->isCartonNumberExisting($object->getNumero())) {
                return new JsonResponse(['error' => 'Ce numéro existe déjà'], Response::HTTP_BAD_REQUEST);
            }
        }

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($object, 'json', ['groups' => 'carton:read']), Response::HTTP_CREATED, [], true);
    }

    #[Route(path:'/{id}', name: 'get_carton', defaults: ['_api_resource_class' => Carton::class], methods: ['GET'])]
    public function show(Request $request, Carton $object): Response
    {
        $user = $this->annuaire->getUser($request);
        $object = $this->cartonRepository->findOneBy(['id' => $object->getId(), 'user'=>$user, 'deleted_at' => null]);

        if (!$object) {
            return new JsonResponse(['error' => 'Carton introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $json = $this->serializer->serialize($object, 'json', ['groups' => 'carton:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: 'edit_carton', defaults: ['_api_resource_class' => Carton::class], methods: ['PATCH'])]
    public function edit(Request $request, Carton $object): Response
    {
        $user = $this->annuaire->getUser($request);

        $content = json_decode($request->getContent(), true);

        $room = $this->transformService->getRoom($content);
        if (isset($content['room']) && !$room) {
            return new JsonResponse(['error' => 'Pièce introuvable'], Response::HTTP_NOT_FOUND);
        }
        if ($room) {
            unset($content['room']);
        }

        $object = $this->cartonRepository->findOneBy(['id' => $object->getId(), 'user' => $user, 'deleted_at' => null]);

        if (!$object) {
            return new JsonResponse(['error' => 'Carton introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        if (isset($content['numero']) && $content['numero'] != $object->getNumero()){
            if ($this->dataService->isCartonNumberExisting($content['numero'])) {
                return new JsonResponse(['error' => 'Ce numéro existe déjà'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($content) {
            $object = $this->serializer->deserialize(json_encode($content), Carton::class, 'json', ['groups' => 'carton:write', 'object_to_populate' => $object]);
            if ($room) {
                $object->setRoom($room);
            }
        }

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($object, 'json', ['groups' => 'carton:read']), Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: 'delete_carton', defaults: ['_api_resource_class' => Carton::class],methods: ['DELETE'])]
    public function delete(Request $request, Carton $object): Response
    {
        $user = $this->annuaire->getUser($request);
        $object = $this->cartonRepository->findOneBy(['id' => $object->getId(), 'user' => $user]);

        if (!$object) {
            return new JsonResponse(['error' => 'Carton introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $object->setDeletedAt(new \DateTimeImmutable());

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse('Carton supprimée !', Response::HTTP_ACCEPTED);
    }
}
