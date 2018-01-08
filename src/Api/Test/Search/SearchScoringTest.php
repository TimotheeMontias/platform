<?php declare(strict_types=1);

namespace Shopware\Api\Test\Search;

use Doctrine\DBAL\Connection;
use Shopware\Api\Entity\Entity;
use Shopware\Api\Entity\Search\Criteria;
use Shopware\Api\Entity\Search\Term\EntityScoreQueryBuilder;
use Shopware\Api\Entity\Search\Term\SearchPattern;
use Shopware\Api\Entity\Search\Term\SearchTerm;
use Shopware\Api\Product\Definition\ProductDefinition;
use Shopware\Api\Product\Repository\ProductRepository;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Struct\ArrayStruct;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SearchScoringTest extends KernelTestCase
{
    /** @var Connection */
    private $connection;

    /** @var ProductRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->connection = $kernel->getContainer()->get('dbal_connection');
        $this->repository = $kernel->getContainer()->get(ProductRepository::class);
        $this->connection->beginTransaction();
        $this->connection->executeUpdate('DELETE FROM product');
    }

    protected function tearDown()
    {
        $this->connection->rollBack();
        parent::tearDown();
    }

    public function testScoringExtensionExists()
    {
        $pattern = new SearchPattern(new SearchTerm('test'));
        $builder = new EntityScoreQueryBuilder();
        $queries = $builder->buildScoreQueries($pattern, ProductDefinition::class, ProductDefinition::getEntityName());

        $criteria = new Criteria();
        foreach ($queries as $query) {
            $criteria->addQuery($query);
        }

        $context = TranslationContext::createDefaultContext();
        $this->repository->create([
            ['id' => 'product-1', 'name' => 'product 1 test'],
            ['id' => 'product-2', 'name' => 'product 2 test'],
        ], $context);

        $result = $this->repository->search($criteria, $context);

        /** @var Entity $entity */
        foreach ($result as $entity) {
            $this->assertArrayHasKey('search', $entity->getExtensions());
            /** @var ArrayStruct $extension */
            $extension = $entity->getExtension('search');

            $this->assertInstanceOf(ArrayStruct::class, $extension);
            $this->assertArrayHasKey('score', $extension);
            $this->assertGreaterThan(0, (float) $extension['score']);
        }
    }
}
