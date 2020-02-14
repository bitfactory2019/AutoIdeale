<?php

namespace App\Utils;

use Nette\Utils\Finder;
use Nette\Utils\FileSystem;

class FilesWrapper
{
  private $presenter;
  private $db;

  public function __construct(\Nette\Application\UI\Presenter $presenter)
  {
    $this->presenter = $presenter;
    $this->db = $this->presenter->getDbService();
  }

  public function deleteTempImage($imageName)
  {
    $config = $this->presenter->getConfig();

    FileSystem::delete($config["wwwDir"].$config["tempImagesDir"].'/'.$imageName);
  }

  public function deletePostImage($id)
  {
    $file = $this->db->table('posts_images')
      ->get($id);

    FileSystem::delete($file->path);

    $this->db->table('posts_images')
      ->where('id', $id)
      ->delete();
  }

  public function uploadTempFiles($temp_path, $files)
  {
    $config = $this->presenter->getConfig();
    return $this->_uploadFiles($config['tempImagesDir'].$temp_path, $files);
  }

  public function uploadPostFiles($postId, $files)
  {
    $config = $this->presenter->getConfig();
    return $this->_uploadFiles($config['postsImagesDir'].$postId, $files);
  }

  public function _uploadFiles($path, $files)
  {
    if (!is_array($files)) {
      $files = [$files];
    }

    if (empty($files[0])) {
      return [];
    }

    $tempFiles = [];

    // DIMENSIONI DA GENERARE
    // 461X307
    // 801x304
    $config = $this->presenter->getConfig();

    foreach ($files as $file) {
      if ($file->isOk() && $file->isImage()) {
        $imageName = $file->getSanitizedName();
        $relative = $path.'/'.$imageName;
        $file->move($config['wwwDir'].$relative);

        $tempFiles[] = [
          'name' => $imageName,
          'url' => $this->presenter->getHttpRequest()->getUrl()->getBaseUrl().$relative,
          'path' => $config['wwwDir'].$relative
        ];
      }
    }

    return $tempFiles;
  }

  private function deleteUserImages($userId)
  {
    $this->_deleteItemImages('user', $userId);
  }

  private function _deleteItemImages($itemType, $itemId)
  {
    $images = $this->db->table($itemType.'s_images')
      ->where($itemType.'s_id', $itemId)
      ->fetchPairs('id');

    foreach ($images as $image) {
      FileSystem::delete($image->path);
    }

    $this->db->table($itemType.'s_images')
      ->where($itemType.'s_id', $itemId)
      ->delete();
  }

  public function moveTempPostImages($tempPath, $postId, $deleteSource = true)
  {
    $config = $this->presenter->getConfig();

    return $this->_moveTempImages($tempPath, $config['postsImagesDir'].$postId, $deleteSource);
  }

  public function moveTempUserImages($tempPath, $userId, $deleteSource = true)
  {
    $config = $this->presenter->getConfig();

    $this->deleteUserImages($userId);

    return $this->_moveTempImages($tempPath, $config['usersImagesDir'].$userId, $deleteSource);
  }

  private function _moveTempImages($tempPath, $itemPath, $deleteSource)
  {
    $config = $this->presenter->getConfig();
    $itemImages = [];

    $src = $config["wwwDir"].$config["tempImagesDir"].$tempPath;
    $dst = $config["wwwDir"].$itemPath;

    $dir = @opendir($src);

    if ($dir === false) {
      return [];
    }

    FileSystem::createDir($dst, 0755);

    while ($file = readdir($dir)) {
      if (($file == '.') || ($file == '..')) {
        continue;
      }

      FileSystem::copy($src.'/'.$file, $dst.'/'.$file);

      $imageName = \basename($file);
      $relative = $itemPath.'/'.$imageName;

      $itemImages[] = [
        'name' => $imageName,
        'url' => $this->presenter->getHttpRequest()->getUrl()->getBaseUrl().$relative,
        'path' => $config["wwwDir"].$relative
      ];
    }

    if ($deleteSource) {
      FileSystem::delete($src);
    }

    closedir($dir);

    return $itemImages;
  }
}
