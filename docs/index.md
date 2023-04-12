# Getting Started With OkaAttachmentManagerBundle

This bundle help the user input high quality data into your web services REST.

## Prerequisites

The OkaAttachmentManagerBundle has the following requirements:

 - PHP 8.0+
 - Symfony 5.4+

## Installation

Installation is a quick (I promise!) 3 step process:

1. Download OkaAttachmentManagerBundle
2. Register the Bundle
3. Configure the Bundle
4. Use bundle and enjoy!

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
composer require coka/attachment-manager-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Register the Bundle

Then, register the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project (Flex did it automatically):

```php
return [
    //...
    Oka\AttachmentManagerBundle\OkaAttachmentManagerBundle::class => ['all' => true],
]
```

### Step 3: Configure the Bundle

Add the following configuration in the file `config/packages/oka_attachment_manager.yaml`.

```yaml
# config/packages/oka_attachment_manager.yaml
oka_attachment_manager:
    prefix_separator: '.'
    volumes:
        file:
            dsn: file:///tmp/acme
            public_url: http://localhost
            options: []
        s3:
            dsn: s3://acme
            public_url: '%env(OBJECT_STORAGE_PUBLIC_URL)%'
            options:
                version: latest
                region: africa
                use_path_style_endpoint: true
                endpoint: '%env(OBJECT_STORAGE_URL)%'
                credentials:
                    key: '%env(OBJECT_STORAGE_ROOT_USER)%'
                    secret: '%env(OBJECT_STORAGE_ROOT_PASSWORD)%'
                #debug: '%kernel.debug%'
    orm:
        model_manager_name: ~
        class: App\Entity\Attachment
        related_objects:
            acme_orm:
                class: App\Entity\Acme
                volume_used: file
                upload_max_size: ~
                directory: ~
                prefix: ~
    mongodb:
        model_manager_name: ~
        class: App\Document\Attachment
        related_objects:
            acme_mongodb:
                class: App\Document\Acme
                volume_used: s3
                upload_max_size: ~
                directory: ~
                prefix: ~
```

### Step 4: Use the bundle is simple

Now that the bundle is installed. 
Create an attachment class:

```php
<?php
// App\Entity\Attachment.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oka\AttachmentManagerBundle\Model\AbstractAttachment;

#[ORM\Entity]
class Attachment extends AbstractAttachment
{
	use Attacheable;
	
    /**
     * @var string
     */
     #[ORM\Id()]
    protected $id;
    // ...
}
```

Create an attacheable class

```php
<?php
// App\Entity\Acme.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oka\AttachmentManagerBundle\Traits\Attacheable;

#[ORM\Entity]
class Acme
{
	use Attacheable;
	
    /**
     * @var string
     */
     #[ORM\Id()]
    protected $id;
    
    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }
    // ...
}
```
