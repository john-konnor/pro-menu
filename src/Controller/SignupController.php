<?php

namespace App\Controller;

use App\DTO\SignupRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SignupController extends AbstractController
{
    #[Route('/api/signup', name: 'api_signup', methods: ['POST'])]
    public function index(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), SignupRequest::class, 'json');
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $user = new User();
        $user->setEmail($dto->email);

        $hashedPassword = $passwordHasher->hashPassword($user, $dto->password);

        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setCreatedAt($dto->createdAt);
        $user->setUpdatedAt($dto->updatedAt);
        $user->setDateActiveTo($dto->dateActiveTo);
        $user->setNameOrg($dto->nameOrg);
        $user->setPhone($dto->phone);

        $em->persist($user);
        $em->flush();

        return $this->json(true);
    }
}
