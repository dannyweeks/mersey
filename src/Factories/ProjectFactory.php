<?php


namespace Weeks\Mersey\Factories;


use Weeks\Mersey\Project;

class ProjectFactory {
    public function create($name, $root, $scripts)
    {
        return new Project($name, $root, $scripts);
    }
}