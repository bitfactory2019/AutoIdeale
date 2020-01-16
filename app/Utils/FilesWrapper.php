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

    public function deleteImage($id)
    {
        //$config = $this->presenter->getConfig();

        //FileSystem::delete($config["wwwDir"].$config["tempImagesDir"].'/'.$imageName);

        $file = $this->db->table('posts_images')
                         ->where('id', $id)
                         ->fetch();

        FileSystem::delete($file->path);

        $this->db->table('posts_images')
                 ->where('id', $id)
                 ->delete();
    }

    public function uploadTempFiles($temp_path, $files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        if (empty($files[0])) {
            return [];
        }

        $postFiles = [];

        // DIMENSIONI DA GENERARE
        // 461X307
        // 801x304
        $config = $this->presenter->getConfig();

        foreach ($files as $file) {
            if ($file->isOk() && $file->isImage()) {
                $imageName = $file->getSanitizedName();
                $relative = $config['tempImagesDir'].$temp_path.'/'.$imageName;
                $file->move($config['wwwDir'].$relative);

                $postFiles[] = [
                    'name' => $imageName,
                    'url' => $this->presenter->getHttpRequest()->getUrl()->getBaseUrl().$relative,
                    'path' => $config['wwwDir'].$relative
                ];
            }
        }

        return $postFiles;
    }

    public function uploadFiles($postId, $files)
    {
        if (empty($files[0])) {
            return [];
        }

        $postFiles = [];

        // DIMENSIONI DA GENERARE
        // 461X307
        // 801x304
        $config = $this->presenter->getConfig();

        foreach ($files as $file) {
            if ($file->isOk() && $file->isImage()) {
                $imageName = $file->getSanitizedName();
                $relative = $config['postsImagesDir'].$postId.'/'.$imageName;
                $file->move($config['wwwDir'].$relative);

                $postFiles[] = [
                    'name' => $imageName,
                    'url' => $this->presenter->getHttpRequest()->getUrl()->getBaseUrl().$relative,
                    'path' => $config['wwwDir'].$relative
                ];
            }
        }

        return $postFiles;
    }

    public function moveTempFiles($temp_path, $post_id, $deleteSource = true)
    {
        $config = $this->presenter->getConfig();
        $postFiles = [];

        $src = $config["wwwDir"].$config["tempImagesDir"].$temp_path;
        $dst = $config["wwwDir"].$config['postsImagesDir'].$post_id;

        $dir = opendir($src);

        FileSystem::createDir($dst, 0755);

        while ($file = readdir($dir)) {
            if (($file == '.') || ($file == '..')) {
                continue;
            }

            FileSystem::copy($src.'/'.$file, $dst.'/'.$file);

            $imageName = \basename($file);
            $relative = $config['postsImagesDir'].$post_id.'/'.$imageName;

            $postFiles[] = [
                'name' => $imageName,
                'url' => $this->presenter->getHttpRequest()->getUrl()->getBaseUrl().$relative,
                'path' => $config["wwwDir"].$relative
            ];
        }

        if ($deleteSource) {
            FileSystem::delete($src);
        }

        closedir($dir);

        return $postFiles;
    }
}
