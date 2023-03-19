<?php declare(strict_types = 1);

namespace App;

use Forge\Route\Request;
use Forge\Route\Response;

class TestController {
    public function indexAction(Request $request): array {
        return [
            'greetings' => 'hola mundo'
        ];
    }

    public function saveAction(Request $request): array {
        $data = $request->getPhpInputStream()->getDecodedJson();

        $title = $data->get('art_title');
        $doi = $data->get('art_doi');
        $lang = $data->get('art_lang');
        
        return [
            'title' => $title,
            'doi' => $doi,
            'status' => 200,
            'message' => sprintf('El articulo "%s" (%s) fue agregado con exito con el DOI: %s', $title, strtoupper($lang), $doi)
        ];
    }

    public function getAuthorsAction(Request $request): array {
        return [
            [
                'name' => 'Luis Arturo',
                'surname' => 'Rodríguez Que',
                'orcid' => 'RY487RYJ857YJRS54',
                'superindex' => 1,
                'author_id' => 1
            ],
            [
                'name' => 'Katya Jhoselin',
                'surname' => 'Sierra Torres',
                'orcid' => 'RY487RYJ857YJRS54',
                'superindex' => 2,
                'author_id' => 2
            ],
            [
                'name' => 'Yulimet Concepción',
                'surname' => 'Mandujano Juárez',
                'orcid' => 'RY487RYJ857YJRS54',
                'superindex' => 3,
                'author_id' => 3
            ],
            [
                'name' => 'Stephanie Elizabeth',
                'surname' => 'Hernández Hernández',
                'orcid' => 'RY487RYJ857YJRS54',
                'superindex' => 4,
                'author_id' => 4
            ],
            [
                'name' => 'Martha Irene',
                'surname' => 'Lázaro Rodríguez',
                'orcid' => 'RY487RYJ857YJRS54',
                'superindex' => 5,
                'author_id' => 5
            ]
        ];
    }
}

?>