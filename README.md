# Mira, a PHP5 Object Database supporting versionning and role based access control (RBAC)

## Usage sample

Let's illustrate Mira with this *Bug Management System* sample. (all resources available 
in the gem under documentation/samples/bms) 

### Simple configuration

Mira eats a `config.ini` file 
    
    database.adapter = PDO_MYSQL
	database.params.host = localhost
	database.params.port = 8889
	database.params.username = root
	database.params.password = root
	database.params.dbname = mira

Then instantiate Mira

	// put Zend, mira_core and mira_utils on your include_path
	set_include_path(
	    'library/zend/sources' .  PATH_SEPARATOR . 
	    'library/mira/modules/utils/sources' .  PATH_SEPARATOR . 
	    'library/mira/modules/core/sources' .  PATH_SEPARATOR . 
	    get_include_path());

	// set up 
	require_once "Mira.php";
	Mira::init("config.ini");

	// now let's play
	$mira = new Mira();

### Create some objects

Before creating any object, you need to describe their type. We call them **vegatypes**

	$bugType = $mira->createVegaType("Bug");
	$bugType->createProperty("priority");
	$bugType->createProperty("description");
	$bugType->save();

Now you can create some instances of these objects. We call those instances **vegas**

	// first bug
	$bug1 = $mira->createVega($bugType, "Implement SSH");
	$bug1->priority = "high";
	$bug1->description = "Security in payment transactions";
	$bug1->save();
	
	// second one
	$bug2 = $mira->createVega($bugType, "Share on twitter");
	$bug2->priority = "low";
	$bug2->description = "Integration of twitter for system notifications";
	$bug2->save();

### Retrieve your vegas

	// load by name
	$sshBug = $mira->selectVegas()
	               ->where("name", "Implement SSH")
	               ->fetchObject();
	print_vega($sshBug);
	
	/**
	 * output:
	 *
	 *   Implement SSH (Bug)
	 *   . priority = high
	 *   . description = Security in payment transactions
	 */
	
	// or even shorter
	// this will select bugs by priority
	$lowPtyBug = $mira->vpriority("low");
	print_vega($lowPtyBug);
	
	/**
	 * output:
	 *
	 *   Share on twitter (Bug)
	 *   . priority = low
	 *   . description = Integration of twitter for system notifications
	 */


### Link your vegas

Let suppose we want to create some projects and link them back to the bugs.

	// create another vegatype
	$projectType = $mira->createVegaType("Project");
	$projectType->save();
	
	// associate it to the bug vegatype
	$bugType->createProperty("project", $projectType);
	$bugType->save();
	
	// create a project vega
	$myProject = $mira->createVega($projectType, "My Project");
	$myProject->save();
	
	// associate it to the existing bugs
	$bug = $mira->vname("Implement SSH"); // shortcut to select a vega by its name
	$bug->project = $myProject;
	$bug->save();
	print_vega($bug);

### Even more powerful

Let's now create some real world queries. You can queue "where" filters as you want.

	$bugs = $mira->selectVegas("Bug")                   // select only 'Bug' vegas
	             ->where("name", "ssh", "permissive")   // with name _containing_ ssh
	             ->where("project", "My Project")       // with project's name being 'My Project'
	             ->fetchAll();

You can even cascade the selects as you need.
             
	$bugs = $mira->selectVegas("Bug")                   // select only 'Bug' vegas
	             ->where("project",                     // you can even cascade queries
	                 $mira->selectVegas("Project")
	                      ->where("name", "my", "permissive"))       
	             ->fetchAll();