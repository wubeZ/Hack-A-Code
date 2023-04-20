// search index for WYSIWYG Web Builder
var database_length = 0;

function SearchPage(url, title, keywords, description)
{
   this.url = url;
   this.title = title;
   this.keywords = keywords;
   this.description = description;
   return this;
}

function SearchDatabase()
{
   database_length = 0;
   this[database_length++] = new SearchPage("Dashboard.html", "Untitled Page", " ", "");
   this[database_length++] = new SearchPage("Problems.html", "Untitled Page", " ", "");
   this[database_length++] = new SearchPage("Contest.html", "Untitled Page", " ", "");
   this[database_length++] = new SearchPage("Account.html", "Untitled Page", " ", "");
   return this;
}
