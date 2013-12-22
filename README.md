### Textus Wordpress plugin

This is a simple plugin that embeds a Textus Viewer instance into a Wordpress plugin. 

#### Textus Short Code
It creates a custom Textus type that corresponds to a shortcode that calls the file. 

    [textus id="\<insert file name\>"]

The file name must be the name of file to be retrieved from the backend. At the moment this assumed to be in form "author/normalised text name".

The JSON file is then retrieved from the file system and presented to the viewer in the post. 

The Textus posts are searchable separately

### Todos

This is only the initial version of the plugin, please check the list of issues for the todo list. 
