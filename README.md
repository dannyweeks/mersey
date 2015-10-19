# Mersey

[![Build Status](https://travis-ci.org/dannyweeks/mersey.svg?branch=master)](https://travis-ci.org/dannyweeks/mersey)
[![Codacy Badge](https://api.codacy.com/project/badge/e1f70770b00848e6b0621e3ac011b930)](https://www.codacy.com/app/danny_4/mersey)

A command line (CLI) tool written in PHP to simplify establishing/interacting an SSH connection to multiple servers quickly.

Some cool things you can do:
- [Connect to servers.](#connecting-to-a-server)
- [Connect to a server and got to a projects directory.](#go-to-a-project)
- [Connect to a server and run a script of your choice.](#run-a-script)

## Prerequisites

- OS X is the only supported operating system at the moment (Linux should be fine though!). More in the future!
- [Composer](https://getcomposer.org/).

## Installation

First, install Mersey globally so you have access to it anywhere by running

```bash
composer global require dannyweeks/mersey
```

Initialise Mersey. This creates a hidden directory in your home to store your servers.

```bash
~/.composer/vendor/dannyweeks/mersey/init.sh
```

Your servers are loaded via a json file which is located `~/.mersey/servers.json`. It comes populated with some example servers to help you on your way. Read the [Defining Servers](#defining-servers) section for more information.

If it isn't already, add composers bin directory to your PATH by adding the below to your ~/.bash_profile (or ~/.bashrc).

```bash
export PATH=~/.composer/vendor/bin:$PATH
```

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

### Edit The Server Config File

```bash
mersey edit
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
