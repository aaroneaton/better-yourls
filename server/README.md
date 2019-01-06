# YOURS Server-side plugin

## API Keyword

#### This adds an 2 API calls on the YOURLS server.

One for checking if a keyword exists on the server.

function:   <b>exist-keyword</b><br>
parameters: keyword - keyword (not full shortlink) to search on YOURLS<br>
returns:    boolean true, false, or string error message
<br><br>

The other to pull the long URL associated with a shortlink or keyword and the associated title for the URL reference stored on the server.

function:   <b>get-keyword-url</b><br>
parameters: url     - the full long URL to search for on YOURLS<br>
            newest  - flag to search for newest long URL, otherwise oldest<br>
returns:    keyword - shortlink keyword (not full shortlink),<br>
                      boolean false, or string error message
