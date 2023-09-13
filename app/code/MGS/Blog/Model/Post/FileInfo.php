<?php 

namespace MGS\Blog\Model\Post;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class FileInfo {
      /**
     * Path in /pub/media directory
     */
    const ENTITY_MEDIA_PATH = 'mgs_blog';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @param Filesystem $filesystem
     * @param Mime $mime
     */
    public function __construct(
        Filesystem $filesystem,
        Mime $mime
    ) {
        $this->filesystem = $filesystem;
        $this->mime = $mime;
    }

    /**
     * Get WriteInterface instance
     *
     * @return WriteInterface
     */
    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $fileName
     * @return string
     */
    public function getMimeType($fileName)
    {
        $filePath = self::ENTITY_MEDIA_PATH . '/' . ltrim($fileName, '/');
        $absoluteFilePath = $this->getMediaDirectory()->getAbsolutePath($filePath);

        $result = $this->mime->getMimeType($absoluteFilePath);
        return $result;
    }

    /**
     * Get file statistics data
     *
     * @param string $fileName
     * @return array
     */
    public function getStat($fileName)
    {
        $filePath = self::ENTITY_MEDIA_PATH . '/' . ltrim($fileName, '/');

        $result = $this->getMediaDirectory()->stat($filePath);
        return $result;
    }

    /**
     * Check if the file exists
     *
     * @param string $fileName
     * @return bool
     */
    public function isExist($fileName, $baseTmpPath = false)
    {
        $filePath = self::ENTITY_MEDIA_PATH . '/' . ltrim($fileName, '/');
        if ($baseTmpPath) {
            $filePath = $baseTmpPath . '/' . ltrim($fileName, '/');
        }

        $result = $this->getMediaDirectory()->isExist($filePath);
        return $result;
    }

    /**
     * Delete the file
     *
     * @param string $fileName
     * @return bool
     */
    public function deleteFile($fileName, $baseTmpPath = false)
    {
        $filePath = self::ENTITY_MEDIA_PATH . '/' . ltrim($fileName, '/');
        if ($baseTmpPath) {
            $filePath = $baseTmpPath . '/' . ltrim($fileName, '/');
        }

        return $this->getMediaDirectory()->delete($filePath);
    }
}

?>