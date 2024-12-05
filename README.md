# Plugin Name: Agreement Report

## Description
This plugin provides a report for the [assignsubmission_xagree plugin](https://github.com/catalyst/moodle-assignsubmission_xagree/tree/main). It allows teachers to view a comprehensive list of students in a course who have agreed the customized statement provided by the assignsubmission_xagree plugin.


## Installing via uploaded ZIP file

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/report/agtreport

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

**Please Ensure the [assignsubmission_xagree](https://github.com/catalyst/moodle-assignsubmission_xagree/tree/main) plugin is installed and properly configured.**


## Contributing
Contributions to this plugin are welcome. Please fork the repository and submit a pull request with detailed changes.


## License
This plugin is licensed under the [GNU GPL v3](https://www.gnu.org/licenses/gpl-3.0.html).
