# Mersey

[![Build Status](https://travis-ci.org/dannyweeks/mersey.svg?branch=master)](https://travis-ci.org/dannyweeks/mersey)
[![Codacy Badge](https://api.codacy.com/project/badge/e1f70770b00848e6b0621e3ac011b930)](https://www.codacy.com/app/danny_4/mersey)

A command line (CLI) tool written in PHP to simplify establishing/interacting an SSH connection to multiple servers quickly.

Some cool things you can do:
- [Connect to servers.](#connecting-to-a-server)
- [Connect to a server and got to a projects directory.](#go-to-a-project)
- [Connect to a server and run a script of your choice.](#run-a-script)

I also wrote a blog [post][post] when Mersey was first released you might find interesting.
 
[post]: http://dannyweeks.com/blog/2015/11/19/introducing-mersey-a-server-management-tool/

[Upgrading from Mersey v1 to v2?](#upgrading-to-version-2)

## Prerequisites

- OS X is the only supported operating system but Linux should be fine!.
- [Composer](https://getcomposer.org/).

## Installation

If it isn't already, add composers bin directory to your PATH by adding the below to your ~/.bash_profile (or ~/.bashrc).

```bash
export PATH=~/.composer/vendor/bin:$PATH
```

Now, install Mersey globally so you have access to it anywhere by running

```bash
composer global require dannyweeks/mersey
```

Initialise Mersey. This creates a hidden directory in your home to store your servers.

```bash
~/.composer/vendor/dannyweeks/mersey/init.sh
```

Your servers are loaded via a json file which is located `~/.mersey/servers.json`. It comes populated with some example servers to help you on your way. Read the [Defining Servers](#defining-servers) section for more information.

## Assumptions/Default Settings

Mersey assumes your SSH key is stored `~/.ssh/id_rsa`.

Mersey uses port 22 to connect the server.

However, these can be [set manually](#additional-server-settings) on a per server basis.

## Usage

Below are the commands to interact with the `mersey` tool. 

|     Description     |         Command        |         Options/Notes        |
|:-------------------:|:----------------------:|:----------------------:|
| Add a server to the config | `mersey add`    | Interactive questions |
| Edit the server config | `mersey edit`       | Opens in default text editor |
| Ping servers and show results  |   `mersey ping` | |
| Connect to a server | `mersey  <servername>` | -f/--force Skip reachable test. -p/--projects List projects |
| Go to a project     | `mersey <servername> <projectname>`  | -f/--force Skip reachable test. -s/--scripts List scripts |
| Run a script        | `mersey <servername> <projectname> <scriptname>`  | -f/--force Skip reachable test. |


## Defining Servers
There is a small amount of setting required to get up and running. Each server is an object in a json array. A server object
needs a minimum of the following:

* **name**: The alias of the server which will be used on the command line.
* **displayName**: The name of the server.
* **username**: The username used to logon of which the SSH key is associated with. 
* **hostname**: The IP address or domain name of the server.

You can get started by running `mersey add` which will ask a series of questions and then add the defined server to your config file.

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

* **sshKey**: Use this private key to connect rather than the default.
* **port**: Use this port to make connections instead of the default for this server.
* **projects**: An array of project objects. [Read more in the projects section](#projects)

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

* **name**: The alias of the project which will be used on the command line.
* **root**: Location of the project root on the server.
* **scripts**: An array of objects. (Optional) [See Scripts](#scripts)

*servers.json*
```json
[
    {
        ...
        "projects": [
            {
                "name": "project",
                "root": "/var/www/project",
                "scripts": []
            }
        ],
        ...
    }
]
```

### Scripts

Scripts are a way of running a command on a project and then exiting the session. They can be defined in two ways; either on a per project basis or globally.

A script object contains three required properties:

* **name** : The alias of the script which will be used on the command line.
* **description** : A brief description for use in Mersey.
* **command** : The command to be run on the server.

Before the command you define is ran mersey connects to the server and changes directory to the project's root.

An example of a script object would be:

```json
{
    "name": "pull",
    "description": "Pulls the latest changes from git.",
    "command": "git fetch --all; git reset --hard origin/master"
}
```

#### Per Project

A script can be defined on a project by adding it to the project's `scripts` array.

*servers.json*
```json
[
    {
        ...
        "projects": [
            {
                "name": "project",
                "root": "/var/www/project",
                "scripts": [
                    {
                        "name": "pull",
                        "description": "Pulls the latest changes from git.",
                        "command": "git fetch --all; git reset --hard origin/master"
                    }
                ]
            }
        ],
        ...
    }
]
```

#### Global Scripts

Global scripts are defined in their own file: `~/.mersey/scripts.json`. Global scripts can be run on any project.

The `scripts.json` must be a json array containing script objects.

*scripts.json*
```json
[
    {
        "name": "pull",
        "description": "Pulls the latest changes from git.",
        "command": "git fetch --all; git reset --hard origin/master"
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
                "scripts": [
                    {
                        "name": "clean",
                        "description": "Empty the log.",
                        "command": "cat /dev/null > /var/www/project/project.log"
                    }
                ]
            }
        ]
    }
]
```

## Upgrade Guide

### Upgrading To Version 2

Update Mersey via Composer.

v2 has added 'global scripts' so we need to create the file where they are stored.

`cp -i ~/.composer/vendor/dannyweeks/mersey/scripts.json.example ~/.mersey/scripts.json`

All your scripts need to be converted into script objects. 

See the scripts [per project](#per-project) section for more details.

## Planned Features
- Online helper tool.

## Contributing

All [pull requests](https://github.com/dannyweeks/mersey/pulls) and bug fixes are welcomed.
Please check the [CONTRIBUTING](https://github.com/dannyweeks/mersey/blob/master/CONTRIBUTING.md) file for more information.
