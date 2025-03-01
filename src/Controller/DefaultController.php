<?php

namespace App\Controller;

//use http\Env\Request;
use App\Entity\Blog;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    #[Route('/default/{id}', name: 'app_default', requirements: ['page' => '\d+'], defaults: ['id' => 1])]
    public function index(BlogRepository $blogRepository, EntityManagerInterface $em, Request $request, int $id): Response
    {
//        $blog = $blogRepository->findOneBy(['id' => $id], ['id' => 'DESC']);
//        dd($blog);
//        $blog = (new Blog())
//            ->setTitle('Hello World')
//            ->setDescription('This is a simple blog post')
//            ->setText('This is the text of the blog post');
//        $em->persist($blog);
//        $em->flush();

        return $this->render(
            'default/index.html.twig',
            [
                'controller_name' => 'DefaultController',
                'id' => $id,
            ] + $request->query->all()
        );
    }
}
