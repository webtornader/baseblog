<?php

namespace App\MessageHandler;

use App\Message\ContentWatchQueue;
use App\Repository\BlogRepository;
use App\Service\ContentWatchApi;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ContentWatchHandler
{
    public function __construct(
        private ContentWatchApi        $contentWatchApi,
        private EntityManagerInterface $entityManager,
        private BlogRepository         $blogRepository,
        private LoggerInterface        $logger
    )
    {
    }

    public function __invoke(ContentWatchQueue $contentWatchQueue): void
    {
        $blogId = (int)$contentWatchQueue->getContent();
        $blog = $this->blogRepository->find($blogId);
        try {
            $blog->setUniqueness($this->contentWatchApi->checkContent($blog->getText()));
        } catch (Exception $e) {
            $this->logger->error(
                'Content uniqueness check failed',
                ['blog' => $blog->getId(), 'error' => $e->getMessage()]);
        }
        $this->entityManager->flush();
        $this->logger->info('Content uniqueness checked', ['blog' => $blog->getId()]);
    }
}