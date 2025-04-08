<?php

namespace App\Controller;

use App\Entity\Element;
use App\Repository\ElementRepository;
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

#[Route('/api/elements')]
final class ElementController extends AbstractController
{
    private AnnuaireService $annuaire;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private DataService $dataService;
    private TransformService $transformService;
    private ElementRepository $elementRepository;

    /**
     * @param AnnuaireService $annuaire
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param TransformService $transformService
     * @param DataService $dataService
     * @param ElementRepository $elementRepository
     */
    public function __construct(AnnuaireService $annuaire, SerializerInterface $serializer, EntityManagerInterface $em, DataService $dataService,TransformService $transformService, ElementRepository $elementRepository)
    {
        $this->annuaire = $annuaire;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->dataService = $dataService;
        $this->transformService = $transformService;
        $this->elementRepository = $elementRepository;
    }

    #[Route(name: 'get_all_elements', defaults: ['_api_resource_class' => Element::class],methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->annuaire->getUser($request);
        $objects = $this->elementRepository->findBy(['user' => $user, 'deleted_at' => null], ['id' => 'asc']);

        $json = $this->serializer->serialize($objects, 'json', ['groups' => 'element:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(name: 'add_element', defaults: ['_api_resource_class' => Element::class],methods: ['POST'])]
    public function new(Request $request): Response
    {
        $user = $this->annuaire->getUser($request);

        $content = json_decode($request->getContent(), true);
        $carton = $this->transformService->getCarton($content);

        if (!$this->dataService->checkValidInAndOutBox($content)) {
            return new JsonResponse(['error' => 'in_box et out_box ne peuvent pas être vrai tous les deux'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($content['carton']) && !$carton) {
            return new JsonResponse(['error' => 'Carton introuvable'], Response::HTTP_NOT_FOUND);
        }
        if ($carton) {
            unset($content['carton']);
        }

        $object = $this->serializer->deserialize(json_encode($content), Element::class, 'json', ['groups' => 'element:write']);
        $object->setUser($user);

        if ($carton) {
            $object->setCarton($carton);
        }

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($object, 'json', ['groups' => 'element:read']), Response::HTTP_CREATED, [], true);
    }

    #[Route(path:'/{id}', name: 'get_element', defaults: ['_api_resource_class' => Element::class], methods: ['GET'])]
    public function show(Request $request, Element $object): Response
    {
        $user = $this->annuaire->getUser($request);
        $object = $this->elementRepository->findOneBy(['id' => $object->getId(), 'user'=>$user, 'deleted_at' => null]);

        if (!$object) {
            return new JsonResponse(['error' => 'Element introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $json = $this->serializer->serialize($object, 'json', ['groups' => 'element:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: 'edit_element', defaults: ['_api_resource_class' => Element::class], methods: ['PATCH'])]
    public function edit(Request $request, Element $object): Response
    {
        $user = $this->annuaire->getUser($request);

        $content = json_decode($request->getContent(), true);

        if (!$this->dataService->checkValidInAndOutBox($content)) {
            return new JsonResponse(['error' => 'in_box et out_box ne peuvent pas être vrai tous les deux'], Response::HTTP_BAD_REQUEST);
        }

        $carton = $this->transformService->getCarton($content);
        if (isset($content['carton']) && !$carton) {
            return new JsonResponse(['error' => 'carton introuvable'], Response::HTTP_NOT_FOUND);
        }
        if ($carton) {
            unset($content['carton']);
        }

        $object = $this->elementRepository->findOneBy(['id' => $object->getId(), 'user' => $user, 'deleted_at' => null]);

        if (!$object) {
            return new JsonResponse(['error' => 'Element introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        if ($content) {
            $object = $this->serializer->deserialize(json_encode($content), Element::class, 'json', ['groups' => 'element:write', 'object_to_populate' => $object]);
            if ($carton) {
                $object->setCarton($carton);
            }

            if ($content['in_box']) {
                $object->setInBox(true);
                $object->setOutBox(false);
            }

            if ($content['out_box']) {
                $object->setInBox(false);
                $object->setOutBox(true);
            }
        }

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($object, 'json', ['groups' => 'element:read']), Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: 'delete_element', defaults: ['_api_resource_class' => Element::class],methods: ['DELETE'])]
    public function delete(Request $request, Element $object): Response
    {
        $user = $this->annuaire->getUser($request);
        $object = $this->elementRepository->findOneBy(['id' => $object->getId(), 'user' => $user]);

        if (!$object) {
            return new JsonResponse(['error' => 'Element introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $object->setDeletedAt(new \DateTimeImmutable());

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse('Element supprimée !', Response::HTTP_ACCEPTED);
    }
}
