<?php

// ------------------------------
// 0. configuration
// ------------------------------
// put zend, mira_core and mira_utils on your include_path
set_include_path(
    dirname(__FILE__) . '/../../../../zend/sources' .  PATH_SEPARATOR . 
    dirname(__FILE__) . '/../../../modules/utils/sources' .  PATH_SEPARATOR . 
    dirname(__FILE__) . '/../../../modules/core/sources' .  PATH_SEPARATOR . 
    get_include_path());
// main file
require_once "Mira.php";
// configure database and more
Mira::init("config.ini");
// the entry point to Mira!
$mira = new Mira();

// ------------------------------
// 1. create a bug vegatype
// ------------------------------
$bugType = $mira->createVegaType("Bug");
$bugType->createProperty("priority");
$bugType->createProperty("description");
$bugType->save();

// ------------------------------
// 2. create some vegas
// ------------------------------
$bug1 = $mira->createVega($bugType, "Implement SSH");
$bug1->priority = "high";
$bug1->description = "Security in payment transactions";
$bug1->save();
// another one
$bug2 = $mira->createVega($bugType, "Share on twitter");
$bug2->priority = "low";
$bug2->description = "Integration of twitter for system notifications";
$bug2->save();


// ------------------------------
// 3. load vegas
// ------------------------------
// load by name
$sshBug = $mira->selectVegas()
               ->where("name", "Implement SSH")
               ->fetchObject();
print_vega($sshBug);
// or even shorter
$lowPtyBug = $mira->vpriority("low");
print_vega($lowPtyBug);


// ------------------------------
// 4. associate vegas
// ------------------------------
// create another vegatype
$projectType = $mira->createVegaType("Project");
$projectType->save();
// associate it to the bug vegatype
$bugType->createProperty("project", $projectType);
$bugType->save();
// create a project
$myProject = $mira->createVega($projectType, "My Project");
$myProject->save();
// associate it to the existing bugs
$bug = $mira->vname("Implement SSH"); // shortcut to select a vega by its name
$bug->project = $myProject;
$bug->save();
print_vega($bug);


// ------------------------------
// 5. complex queries
// ------------------------------
$bugs = $mira->selectVegas("Bug")                   // select only 'Bug' vegas
             ->where("name", "ssh", "permissive")   // with name _containing_ ssh
             ->where("project", "My Project")       // with project's name being 'My Project'
             ->fetchAll();
             
$bugs = $mira->selectVegas("Bug")                   // select only 'Bug' vegas
             ->where("project",                     // you can even cascade queries
                 $mira->selectVegas("Project")
                      ->where("name", "my", "permissive"))       
             ->fetchAll();