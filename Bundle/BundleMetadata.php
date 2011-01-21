<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\EasyExtendsBundle\Bundle;

use Symfony\Component\Finder\Finder;

class BundleMetadata
{
    protected $bundle;

    protected $vendor = false;

    protected $valid = false;

    protected $namespace;

    protected $name;
    
    protected $extendedDirectory = false;

    protected $extendedNamespace = false;

    protected $configuration = array();

    public function __construct($bundle, array $configuration = array())
    {
        $this->bundle = $bundle;
        $this->configuration = $configuration;

        $this->buildInformation();
    }

    /**
     * build basic information and check if the bundle respect the following convention
     *   Vendor/BundleNameBundle/VendorBundleNameBundle
     *
     * if the bundle does not respect this convention then the easy extends command will ignore
     * this bundle
     *
     * @return void
     */
    protected function buildInformation()
    {

        $information = explode('\\', $this->getClass());

        if(!$this->isExtendable()) {
            $this->valid = false;
            return;
        }

        if(count($information) != 3) {
            $this->valid = false;
            return;
        }

        if($information[0].$information[1] != $information[2]) {
            $this->valid = false;
            return;
        }


        $parts = explode('\\', $this->getclass());

        $this->name = $parts[count($parts) - 1];
        $this->vendor = $information[0];
        $this->namespace =  sprintf('%s\%s', $this->vendor, $information[1]);
        $this->extendedDirectory = sprintf('%s/%s/%s', $this->configuration['application_dir'], $this->vendor, $information[1]);
        $this->extendedNamespace = sprintf('Application\\%s\\%s', $this->vendor, $information[1]);
        $this->valid = true;
    }

    public function isExtendable()
    {
        // does not extends Application bundle ...
        return !(
            strpos($this->getClass(), 'Application') === 0
            || strpos($this->getClass(), 'Symfony') === 0
        );

    }
    public function getClass()
    {
        return get_class($this->bundle);
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function getExtendedDirectory()
    {
        return $this->extendedDirectory;
    }

    public function getVendor()
    {
        return $this->vendor;
    }

    public function getMappingEntityDirectory()
    {

        return sprintf('%s/Resources/config/doctrine/metadata/orm', $this->bundle->getPath());
    }

    public function getExtendedMappingEntityDirectory()
    {

        return sprintf('%s/Resources/config/doctrine/metadata/orm', $this->getExtendedDirectory());
    }

    public function getEntityDirectory()
    {

        return sprintf('%s/Entity', $this->bundle->getPath());
    }

    public function getExtendedEntityDirectory()
    {

        return sprintf('%s/Entity', $this->getExtendedDirectory());
    }
    
    public function getEntityMappingFiles()
    {
        try {
            $f = new Finder;
            $f->name('Application.*.dcm.xml');
            $f->in($this->getMappingEntityDirectory());

            return $f->getIterator();
        } catch(\Exception $e) {
            
            return array();
        }
    }

    public function getEntityNames()
    {
        $names = array();
        
        try {
            $f = new Finder;
            $f->name('Application.*.dcm.xml');
            $f->in($this->getMappingEntityDirectory());

            foreach($f->getIterator() as $file) {
                $e = explode('.', $file);
                $names[] = $e[count($e) - 3];
            }

        } catch(\Exception $e) {

        }

        return $names;
    }

    public function getRepositoryFiles()
    {
        try {
            $f = new Finder;
            $f->name('Application.*.dcm.xml');
            $f->in($this->getEntityDirectory());

            return $f->getIterator();
        } catch(\Exception $e) {

            return array();
        }
    }

    public function getExtendedNamespace()
    {
        return $this->extendedNamespace;
    }

    public function getNamespace()
    {

        return $this->namespace;
    }

    /**
     * return the bundle name
     *
     * @return string return the bundle name
     */
    public function getName()
    {
        
        return $this->name;
    }

}