<?php

/**
 * This file is now obsolete. The task scheduling has been migrated to Crunz.
 *
 * To run the scheduled tasks, you should now use the Crunz command-line utility.
 * From the project root, run the following command in your system's crontab:
 *
 *   * * * * * cd /path/to/your/project && vendor/bin/crunz schedule:run >> /dev/null 2>&1
 *
 * The tasks themselves are now defined in the `tasks/` directory.
 */

echo "This scheduler has been migrated to Crunz. Please see the instructions in this file.\n";
exit(1);
