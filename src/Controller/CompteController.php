<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\VerificationCode;
use App\Form\SignupFormType;
use App\Service\VerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CompteController extends AbstractController
{
    
    #[Route('/signup', name: 'app_signup')]
public function signup(
    Request $request,
    UserPasswordHasherInterface $hasher,
    VerificationService $verificationService
): Response {

    $user = new User();
    $form = $this->createForm(SignupFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $plainPassword = $form->get('mdp')->getData();

        $user->setMdp(
            $hasher->hashPassword($user, $plainPassword)
        );

        $user->setDateInscription(new \DateTime());
        $user->setRole('ROLE_USER');

        // stock session
        $request->getSession()->set('pending_user_data', [
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'mdp' => $user->getMdp(),
            'adresse' => $user->getAdresse(),
            'telephone' => $user->getTelephone(),
        ]);

        // send code (ONLY EMAIL)
        $verificationService->sendCode(
            $user->getEmail(),
            'signup'
        );

        return $this->redirectToRoute('app_verify_code');
    }

    return $this->render('compte/signup.html.twig', [
        'form' => $form->createView()
    ]);
}

    #[Route('/verify-code', name: 'app_verify_code')]
public function verifyCode(
    Request $request,
    EntityManagerInterface $em
): Response {

    $session = $request->getSession();
    $data = $session->get('pending_user_data');

    if (!$data) {
        return $this->redirectToRoute('app_signup');
    }

    $error = null;

    if ($request->isMethod('POST')) {

        $inputCode = $request->request->get('code');

        $codeEntity = $em->getRepository(\App\Entity\VerificationCode::class)
            ->findOneBy(
                [
                    'email' => $data['email'],
                    'type' => 'signup'
                ],
                ['id' => 'DESC']
            );

        if (!$codeEntity) {
            $error = "Code introuvable";
        }
        elseif ($codeEntity->getExpiresAt() < new \DateTime()) {
            $error = "Code expiré";
        }
        elseif ($codeEntity->getCode() !== $inputCode) {
            $error = "Code incorrect";
        }
        else {

            $user = new User();
            $user->setNom($data['nom']);
            $user->setPrenom($data['prenom']);
            $user->setEmail($data['email']);
            $user->setMdp($data['mdp']);
            $user->setAdresse($data['adresse']);
            $user->setTelephone($data['telephone']);
            $user->setRole('ROLE_USER');
            $user->setDateInscription(new \DateTime());

            $em->persist($user);
            $em->flush();

            $session->remove('pending_user_data');

            return $this->redirectToRoute('app_login');
        }
    }

    return $this->render('compte/verify.html.twig', [
        'error' => $error
    ]);
}



    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $em,
        VerificationService $service,
        UserPasswordHasherInterface $hasher
    ): Response {

        $session = $request->getSession();
        $step = $request->request->get('step', 1);
        $email = $session->get('reset_email');

        //envoie code
        if ($step == 1 && $request->isMethod('POST')) {
            $email = $request->request->get('email');

            $service->sendCode($email, 'reset');
            $session->set('reset_email', $email);

            return $this->render('compte/reset.html.twig', [
                'step' => 2
            ]);
        }

        // verif code
        if ($step == 2 && $request->isMethod('POST')) {

            $code = $request->request->get('code');

            $token = $em->getRepository(VerificationCode::class)
                ->findOneBy(['email' => $email, 'type' => 'reset'], ['id' => 'DESC']);

            if (!$token || $token->getCode() !== $code) {
                return $this->render('compte/reset.html.twig', [
                    'step' => 2,
                    'error' => 'Code incorrect'
                ]);
            }

            if ($token->getExpiresAt() < new \DateTime()) {
                return $this->render('compte/reset.html.twig', [
                    'step' => 2,
                    'error' => 'Code expiré'
                ]);
            }

            $session->set('reset_verified', true);

            return $this->render('compte/reset.html.twig', [
                'step' => 3
            ]);
        }

        // reset
        if ($step == 3 && $request->isMethod('POST')) {

            if (!$session->get('reset_verified')) {
                return $this->redirectToRoute('app_reset_password');
            }

            $user = $em->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            $password = $request->request->get('password');

            $user->setMdp($hasher->hashPassword($user, $password));

            $em->flush();

            $session->remove('reset_email');
            $session->remove('reset_verified');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('compte/reset.html.twig', [
            'step' => 1
        ]);
    }
}