# SJTPocketMineMapTools

SJTPocketMineMapTools is a [PocketMine-MP] plugin, using the new API, that provides a set of tools for working collaboratively in a world.

  - Stops global editing of the world for all users
  - Allows users to define named regions within the world
  - Allows users to request a permit to edit a named region
  - Only one user is allowed to edit a region
  - Edits to regions are revision controlled using Git

### Version
1.1

### Usage

The plugin supports the following commands:

```yaml
    listregions:
        description: "Lists all defined regions"
        usage: "/listregions"
    startregion:
        description: "Starts a new region using the player's current location"
        usage: "/startregion [player]"
    cancelregion:
        description: "Cancels the region that\'s been started"
        usage: "/cancelregion [player]"
    endregion:
        description: "Finishes defining a new region using the player's current location, specifying the region name"
        usage: "/endregion <regionname> [player]"
    tptoregion:
        description: "Teleports player to a named region"
        usage: "/tptoregion <regionname> [player]"
    deleteregion:
        description: "Deletes a named region"
        usage: "/deleteregion <regionname> [player]"
    saveregion:
        description: "Saves a snapshot of the region to disk and commits a revision"
        usage: "/saveregion <regionname>"
    revertregion:
        description: "Reverts the content of a region to the last saved version"
        usage: "/revertregion <regionname>"
    requestpermit:
        description: "Requests a permit to edit a region"
        usage: "/requestpermit <regionname>"
    releasepermit:
        description: "Releases a region editing permit"
        usage: "/releasepermit <regionname>"
```

### Tech

SJTPocketMineMapTools is written as a [PocketMine-MP] plugin in PHP, using the new PocketMine-MP 1.4 API, so will only work on PocketMine-MP Alpha_1.4 and above.  The latest version of PocketMine-MP is recommended.  Currently no third party libraries are used by this plugin.

### Installation

To run this plugin during development (i.e. non-Phar), first install the Official DevTools plugin. Instructions for setting up a development plugin environment are here: https://github.com/PocketMine/Documentation/wiki/Plugin-Tutorial

For development, clone the plugin code to `[PocketMine Folder]/plugins/SJTMapToolsSource`:

```sh
$ cd [PocketMine Folder]/plugins/
$ git clone [git-repo-url] SJTMapToolsSource
```

For production, install the phar file at `[PocketMine Folder]/plugins/SJTMapTools.phar`

On first run, the plugin will create a folder `[PocketMine Folder]/plugins/SJTMapTools` and will also clone the regions repository into `[PocketMine Folder]/plugins/SJTMapTools/regions`


### Todo's

 - Currently anyone can define regions, this should be permission controlled.
 - Currently when requesting a permit, it is allocated immediately (if noone else has a permit for that region).  Need to implement a permission layer so that permits are approved / declined by an admin.


License
----

MIT


[PocketMine-MP]:http://www.pocketmine.net/
