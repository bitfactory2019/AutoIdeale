<?php

namespace App\Utils;

class FilesWrapper
{
    private $presenter;
    private $db;

    public function __construct(\Nette\Application\UI\Presenter $presenter)
    {
        $this->presenter = $presenter;
        $this->db = $this->presenter->getDbService();
    }

    public function uploadPostFiles($post_id, $files)
    {
        if (empty($files[0])) {
            return [];
        }

        $postFiles = [];

        // DIMENSIONI DA GENERARE
        // 461X307
        // 801x304
        $config = $this->presenter->context->getParameters();

        foreach ($files as $file) {
            if ($file->isOk() && $file->isImage()) {
                $imageName = $file->getSanitizedName();
                $relative = $config['postsImagesDir'].$post_id.'/'.$imageName;
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
}