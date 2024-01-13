<?php

namespace Oka\AttachmentManagerBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Oka\AttachmentManagerBundle\DependencyInjection\Compiler\CachePoolServicePass;
use Oka\AttachmentManagerBundle\DependencyInjection\Compiler\CheckAwsS3EnabledPass;
use Oka\AttachmentManagerBundle\DependencyInjection\Compiler\DoctrinePass;
use Oka\AttachmentManagerBundle\EventListener\DoctrineMongoDBListener;
use Oka\AttachmentManagerBundle\EventListener\DoctrineORMListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class OkaAttachmentManagerBundle extends Bundle
{
    /**
     * @var array
     */
    public static $doctrineDrivers = [
        'orm' => [
            'registry' => 'doctrine',
            'subscriber_class' => DoctrineORMListener::class,
            'tag' => 'doctrine.event_subscriber',
            'bundle' => 'doctrine/doctrine-bundle',
        ],
        'mongodb' => [
            'registry' => 'doctrine_mongodb',
            'subscriber_class' => DoctrineMongoDBListener::class,
            'tag' => 'doctrine_mongodb.odm.event_subscriber',
            'bundle' => 'doctrine/mongodb-odm-bundle',
        ],
    ];

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $this->addRegisterMappingsPass($container);

        $container->addCompilerPass(new CheckAwsS3EnabledPass());
        $container->addCompilerPass(new CachePoolServicePass());
        $container->addCompilerPass(new DoctrinePass());
    }

    private function addRegisterMappingsPass(ContainerBuilder $container)
    {
        $mapping = [realpath(__DIR__.'/../config/doctrine') => 'Oka\AttachmentManagerBundle\Model'];

        if (true === class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mapping, ['oka_attachment_manager.orm.model_manager_name'], 'oka_attachment_manager.backend_type.orm'));
        }

        if (true === class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mapping, ['oka_attachment_manager.mongodb.model_manager_name'], 'oka_attachment_manager.backend_type.mongodb'));
        }
    }
}
