# DONE! Process Pipeline

Workflow processing made easy - powered by prooph software GmbH

## Middleware to the rescue

A DONE! story is split into chapters. Each chapter represents a process required to get the story done.
Such a process can consist of multiple steps relying on each other.
Let's take a database import as an example.

Imagine a previous chapter has collected user information from an external
system and put the data in a CSV file. Task of the next chapter is to load the user data from the CSV file (ideally line by line)
and import the data into an empty table.

*Oh wait! What if the table is not empty?*
Good catch! We want to do a full sync but without downtime of the database. So we need to import the new data in a temp table first.
If this was successful we can drop the old user table and rename the temp table to become the new user table.

As you can see a simple story can become quite complex. And in another story we may want to do some steps equal but
others not. That's the time where middleware comes into play.

Instead of writing a single script which includes all the listed steps above we split the process into steps and
implement these steps as middleware. Our example would end up in this middleware:

- CreateEmptyTableMiddleware
- LoadDataFromCsvFileMiddleware
- ImportDataIntoTableMiddleware
- ExchangeTableMiddleware


## Middleware Configuration

So far so good but how can the middleware know which table to create, which file to load and so on?

**The answer is:**

1. With information derived from the chapter command which was sent by the DONE! backend to start the chapter.
2. And from a type description of the data which is going to be processed.

### ChapterCommand

The chapter command MUST be a [Prooph\Common\Messaging\Message](https://github.com/prooph/common/blob/master/src/Messaging/Message.php).
It is set up and sent from a DONE! backend and typically includes information about the chapter (the configuration).

### Prototype and Type of Data

The most important information a chapter command should provide is which data to process. To make it easier for
middleware to identify the type of the data and possibly also get information about properties or the item type of collection
this package provides special value objects representing different [value types](src/Type).
The type objects are designed to surround their native counterparts and are extensible. So you can for example
create a user type which extends the [AbstractDictionary](src/Type/AbstractDictionary.php).
A user type might look something like the [UserDictionary](tests/Mock/UserDictionary.php) we use in the test suite.

Additionally the package ships with a [Prototype](src/Type/Prototype.php) class. A prototype describes a value type
without referencing an actual value.

More information coming soon. Stay tuned!

your prooph software team
