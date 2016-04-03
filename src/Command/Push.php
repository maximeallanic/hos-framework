<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 03/04/16
 * Time: 17:39
 */

namespace Hos\Command;

use Cz\Git\GitRepository;
use Hos\Option;

//require_once "../../../../autoload.php";
class Push extends Base\Command
{
    private $repository;

    function __construct($arguments) {
        $path = getcwd();
        $this->repository = new GitRepository($path);
        if ($this->repository->hasChanges()) {
            $this->repository->addFile(Option::CONF_DIR);
            $this->repository->addFile(Option::ASSET_DIR);
            $this->repository->addFile(Option::PROJECT_DIR);
            $this->repository->addFile("composer.json");
            $this->repository->addFile("README.md");
            $this->addTag($arguments['type']);
            $this->repository->commit($arguments['message']);
        }

        exec("git push origin master --tags");

    }

    function addTag($type) {
        $tags = $this->repository->getTags();
        $tag = end($tags);
        preg_match('/v?([0-9]+)\.([0-9]+)\.([0-9]+)(?:-([0-9]+))?/', $tag, $matches);
        array_shift($matches);
        $tag = array_map('intval', $matches);
        switch ($type) {
            case 'patch':
                if (!isset($tag[3]))
                    $tag[3] = 0;
                $tag[3]++;
                break;

            case 'minor':
                $tag[3] = 0;
                $tag[2]++;
                break;

            case 'major':
                $tag[3] = 0;
                $tag[2] = 0;
                $tag[1]++;
                break;

            case 'release':
                $tag[3] = 0;
                $tag[2] = 0;
                $tag[1] = 0;
                $tag[0]++;
        }
        $out = sprintf("%d.%d.%d", $tag[0], $tag[1], $tag[2]);
        if ($tag[3])
            $out .= sprintf("-%d", $tag[3]);
        $this->repository->createTag($out);
        return $out;
    }
}