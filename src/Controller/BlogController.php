<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Filter\BlogFilter;
use App\Form\BlogFilterType;
use App\Form\BlogType;
use App\Message\ContentWatchQueue;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

//#[Route('/admin/blog')]
final class BlogController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
    }
    #[Route('/admin/blog', name: 'app_blog_list', methods: ['GET'])]
    public function index(Request $request, BlogRepository $blogRepository, PaginatorInterface $paginator): Response
    {
        $blogFilter = new BlogFilter();
        $form = $this->createForm(BlogFilterType::class, $blogFilter);
        $form->handleRequest($request);

        $blogFilterWithPagination = $paginator->paginate(
            $blogRepository->findByFilter($blogFilter), /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );

        return $this->render('blog/list.html.twig', [
            'blogs' => $blogFilterWithPagination,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/blog', name: 'app_blog_index', methods: ['GET'])]
    public function list(Request $request, BlogRepository $blogRepository, PaginatorInterface $paginator): Response
    {
        $blogFilter = new BlogFilter(user: $this->getUser());
        $form = $this->createForm(BlogFilterType::class, $blogFilter);
        $form->handleRequest($request);

        $blogFilterWithPagination = $paginator->paginate(
            $blogRepository->findByFilter($blogFilter), /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );

        return $this->render('blog/index.html.twig', [
            'blogs' => $blogFilterWithPagination,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/blog/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ): Response
    {
        $blog = new Blog($this->getUser());
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            try {
                $messageBus->dispatch(new ContentWatchQueue($blog->getId()));
            } catch (ExceptionInterface $e) {
                $errorMsg = 'ContentWatch Queue error' . $e->getMessage();
                $this->addFlash('error', $errorMsg);
                $this->logger->error($errorMsg);
            }
            $this->addFlash('success', 'Blog created successfully');

            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/blog/{id}', name: 'app_blog_show', methods: ['GET'])]
    #[IsGranted('view', 'blog', 'Permission denied', 403)]
    public function show(Blog $blog): Response
    {
        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
        ]);
    }

    #[Route('/blog/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'blog', 'Permission denied', 403)]
    public function edit(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        if ($blog->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Blog created successfully');

            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/blog/{id}', name: 'app_blog_delete', methods: ['POST'])]
    #[IsGranted('edit', 'blog')]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        if ($blog->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$blog->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($blog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_blog_list', [], Response::HTTP_SEE_OTHER);
    }
}
