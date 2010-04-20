# Mira, a PHP5 Object Database supporting versionning and role based access control (RBAC)

## Usage sample

### Configure it

Mira eats a `config.ini` file
    
    database.adapter = PDO_MYSQL
	database.params.host = localhost
	database.params.port = 8889
	database.params.username = root
	database.params.password = root
	database.params.dbname = vega

Give it to Mira

    Mira::init("config.ini");
    $api = new Mira();

### Create some object types (that we call vega types)

Imagine you want to do a bug management system. You will have to deal with Bugs and Projects and Users.
Let's create them

    $projectType = $api->createVegaType(0, "Project");
    // name and date properties are created by default
    $projectType->createVegaProperty("technology");
    $projectType->save();
    
    $bugType = $api->createVegaType(0, "Bug");
    $bugType->createVegaProperty("project", $projectType);
    $bugType->createVegaProperty("priority");
    $bugType->save();

### Create some objects

### Query your objects easily