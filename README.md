### Textus Wordpress plugin

This is a simple plugin that embeds a Textus Viewer instance into a Wordpress plugin. 

### Dependencies

This plugin depends on the Textus Viewer code (https://github.com/okfn/textus-viewer). 

#### Textus Short Code
It creates a custom Textus type that corresponds to a shortcode that calls the file. 

    [textus id="<insert file name>"]

The file name must be the name of file to be retrieved from the backend. At the moment this assumed to be in form "author/normalised text name".

The JSON file is then retrieved from the file system and presented to the viewer in the post. 

The Textus posts are searchable separately

### Todos

This is only the initial version of the plugin, please check the list of issues for the todo list. 

### API

The API format is JSON. 

POST
Request:
<code>
{
 "textid": integer of the text id being retrieved, 
 "start":integer of the start co-ordinate, 
 "end": integer of the end co-ordinate, 
 "private": either "true" or "false". Must be a string, 
 "payload": { 
    "language":String. Language of the note, 
    "text": String. The note's body
  }, 
  "name": String. The public name of the account. 
}
</code>
Response
<code>
{
  "status": Integer. HTTP Status code. 200 for success.
   "note" : String. Description.
}
</code>
GET

Request: ?text=1&type=annotation

Response
<code>
{

    "status": Integer. HTTP Status code,
    "notes": [
        {
            "start": Integer. Start co-ordinate,
            "end": Integer. End co-ordinate,
            "time": String. The date in MySQL,
            "private": String. True or false,
            "payload": {
                "language": String. Language code for the note,
                "text": String. The note
            }
        },

    ]

}
</code>

PUT

Request

<code>
{
 "textid": integer of the text id being retrieved,
 "start": integer of the start co-ordinate,
 "end": integer of the end co-ordinate,
 "private": either "true" or "false". Must be a string,
 "payload":
 {
  "language": String. Language code for the note,
  "text": String. The updated text
 }, 
 "name": String. The public name of the account.
 "id" :  Integer
}
</code>

Response

<code>
{
  "status": Integer. HTTP Status code. 200 for success.
   "note" : String. Description.
}
</code>
