<?php

/*
 * This file is part of the SecotrustSabreDavBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Secotrust\Bundle\SabreDavBundle\SabreDav\Gaufrette;

use Sabre\DAV\File as BaseFile;

class File extends BaseFile
{
    /**
     * @var \Gaufrette\File
     */
    protected $file;

    /**
     * Constructor.
     *
     * @param \Gaufrette\File $file
     */
    public function __construct(\Gaufrette\File $file)
    {
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->file->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        return (int) $this->file->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModified(): ?int
    {
        $mtime = $this->file->getMtime();

        return false === $mtime ? null : (int) $mtime;
    }

    /**
     * {@inheritdoc}
     */
    public function put($data): ?string
    {
        $this->file->setContent($data);

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): mixed
    {
        return $this->file->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->file->delete();
    }
}
