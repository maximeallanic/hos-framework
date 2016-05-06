<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 04/04/16
 * Time: 15:19
 */

try {
    $project = (new Project())->findOneByUID($repository);
    if (!$project)
        throw new Error("no_project_found", array(
            "repository" => $repository
        ));

    $auth = Instance::getAuthInstance();
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
        ($_SERVER['PHP_AUTH_USER'] && $_SERVER['PHP_AUTH_PW'] &&
            !$auth->connect($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']))) {
        header('WWW-Authenticate: Basic');
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }
    $user = $auth->getUser();
    Log::write("u", json_encode($user));
    if (isset($_GET['service']) && $_GET['service'] == 'git-upload-pack') {
        if (!$project->haveReadAccess($user))
            throw new Error("no_read_access");
        $projectFunction = "onGitRead";
    }
    else if(isset($_GET['service']) && $_GET['service'] == 'git-receive-pack') {
        if (!$project->haveWriteAccess($user))
            throw new Error("no_write_access");
        $projectFunction = "onGitWrite";
    }
    else
        $projectFunction = "";
    $git = new Git($repository);
    $git->executeBackend($request);
    if (method_exists($project, $projectFunction))
        $project->$projectFunction($user);
} catch (Error $e) {
    Log::write("git", $e->getMessage());
}
return null;