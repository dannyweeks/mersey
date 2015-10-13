# Mersey

[![Build Status](https://travis-ci.org/dannyweeks/mersey.svg?branch=master)](https://travis-ci.org/dannyweeks/mersey)

A command line (CLI) tool written in PHP to simplify establishing/interacting an SSH connection to multiple servers quickly.

## Prerequisites

- OS X is the only supported OS at the moment. More in the future!
- [Composer](https://getcomposer.org/) is the recommended installation method.

## Installation

First, install Mersey globally so you have access to it anywhere by running *change to tag when one is available*

```bash
composer global require dannyweeks/mersey:dev-master
```

Initialise Mersey. This creates a hidden directory in your home to store your servers.

```bash
~/.composer/vendor/dannyweeks/mersey/init.sh
```

Your servers are loaded via a json file which is located `~/.mersey/servers.json`. It comes populated with some example servers to help you on your way. Read the [Defining Servers](#defining-servers) section for more information.

## Assumptions/Default Settings

Mersey assumes your SSH key is stored `~/.ssh/id_rsa`.

Mersey uses port 22 to connect the server.

However, this can be [set manually](#additional-server-settings) on a per server basis.

## Usage

Below are the commands to interact with the `mersey` tool. 

### Connecting To A Server

```bash
mersey <servername>
```

### Go To A Project

```bash
mersey <servername> <projectname>
```

### Run A Script

```bash
mersey <servername> <projectname> <scriptname>
```

### List Projects For A Given Server

```bash
mersey <servername> --project
```

### Test Availability Of The Registered Servers

```bash
mersey ping
```

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
* **port** : Use this port to make connections instead of the default for this server.
* **projects** : An array of project objects. [Read more in the projects section](#projects)

*servers.json*
```json
[
    {
        ...
        "sshKey": "/path/to/another/id_rsa",
        "port": 2222,
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
        ],
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
        "port": 2222,
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

## Contributing

All [pull requests](https://github.com/dannyweeks/mersey/pulls) and bug fixes are welcomed.
Please check the [CONTRIBUTING](https://github.com/dannyweeks/mersey/blob/master/CONTRIBUTING.md) file for more information.

## TODO

[Checklist](http://phppackagechecklist.com/#1,2,3,4,5,6,7,8,9,11,12,13,14).
