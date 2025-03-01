<?php
// src/Form/DataTransformer/IssueToNumberTransformer.php
namespace App\Form\DataTransformer;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TagTransformer implements DataTransformerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  null|PersistentCollection<Tag> $value
     */
    public function transform($value): string
    {
        if (null === $value) {
            return '';
        }

        $array = [];
        foreach ($value as $item) {
            $array[] = $item->getName();
        }

        return implode(', ', $array);
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $value
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($value): ArrayCollection
    {
        if (!$value) {
            return new ArrayCollection();
        }

        $items = explode(',', $value);
        $items = array_map('trim', $items);
        $items = array_unique($items);

        $tags = new ArrayCollection();

        foreach ($items as $item) {
            $tag = $this->entityManager->getRepository(Tag::class)->findOneBy(['name' => $item]);
            if (!$tag) {
                $tag = new Tag();
                $tag->setName($item);
                $this->entityManager->persist($tag);
            }
            $tags->add($tag);
        }

        return $tags;
    }
}