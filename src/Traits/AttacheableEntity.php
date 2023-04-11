<?php
namespace Oka\AttachmentManagerBundle\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
trait AttacheableEntity
{
    use Attacheable;
    
    public function getAttachments(): Collection
    {
        $criteria = Criteria::create()
                        ->where(Criteria::expr()->eq('relatedObject.class', static::class))
                        ->andWhere(Criteria::expr()->eq('relatedObject.identifier', $this->id))
                        ->orderBy(['lastModified' => Criteria::DESC]);
        
        return $this->attachments->matching($criteria);
    }
}
