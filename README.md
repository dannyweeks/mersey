# Mersey

[![Build Status](https://travis-ci.org/dannyweeks/mersey.svg?branch=master)](https://travis-ci.org/dannyweeks/mersey)

A command line (CLI) tool written in PHP to simplify establishing/interacting an SSH connection to multiple servers quickly.

## Prerequisites

- OS X is the only supported OS at the moment. More in the future!
- [Composer](https://getcomposer.org/) is the recommended installation method.

## Installation

1. First, install Mersey globally so you have access to it anywhere by running `composer global require dannyweeks/mersey:dev-master` *change to tag when one is available*
2. Initialise Mersey by running `~/.composer/vendor/dannyweeks/mersey/init.sh`. This creates a hidden directory in your home to store your servers.
3. Your servers are loaded via a json file which is located `~/.mersey/servers.json`. It comes populated with some example servers to help you on your way. Read the [Defining Servers](#defining-servers) section for more information.

## Usage

To connect to a server use `mersey <servername>`. 

To connect to a server and navigate to a projects root directory use `mersey <servername> <projectname>`.

To connect to a server and run a script on a project use `mersey <servername> <projectname> <scriptname>`.

Use `mersey <servername> --projects` to view a list of all available projects for a given server.

Use `mersey ping` to ping all registered servers and test availability.

## Defining Servers
There is a small amount of setting required to get up and running. Each server is an object in a json array. A server object
needs a minimum of the following:

* **name** : The alias of the server which will be used on the command line.
* **displayName** : The name of the server.
* **username** : The username used to logon of which the SSH key is associated with. 
* **hostname** : The IP address or domain name of the server.

*servers.json*
```json
[
    {
        "name": "personal",
        "displayName": "Personal Server",
        "username": "danny",
        "hostname": "192.168.0.1"
    }
]
```

### Additional Server Settings

There are optional setting for servers which help facilitate your needs. 

* **sshKey** : Use this private key to connect rather than the default.
* **projects** : An array of project objects. [Read more in the projects section](#projects)

*servers.json*
```json
[
    {
        ...
        "sshKey": "/path/to/another/id_rsa"
        ...
    }
]
```

### Projects

Add a project to a server by creating an object in the `projects` array of the server.

* **name** : The alias of the project which will be used on the command line.
* **root** : Location of the project root on the server.
* **scripts** : An object of key value pairs for scripts to run on the project. The key will be used on the command line 
similar to the names of servers and projects.

*servers.json*
```json
[
    {
        ...
        "projects": [
            {
                "name": "project",
                "root": "/var/www/project",
                "scripts": {
                    "clean": "/dev/null > /var/www/project/today.log"
                }
            }
        ]
        ...
    }
]
```

### Full Example Server Definition.

Below is a an example of a server called `personal` with one project called `project`. `project` has a script attached
 to it called `clean`. 

```json
[
    {
        "name": "personal",
        "displayName": "Personal Server",
        "username": "danny",
        "hostname": "192.168.0.1",
        "sshKey": "/path/to/another/id_rsa",
        "projects": [
            {
                "name": "project",
                "root": "/var/www/project",
                "scripts": {
                    "clean": "/dev/null > /var/www/project/today.log"
                }
            }
        ]
    }
]
```

## TODO

[Checklist](http://phppackagechecklist.com/#1,2,3,4,5,6,8,9,12,13).
