# seat-billing
A billing system for mining/PvE costs for corps/alliances.

Thanks to dysath/denngarr for writing the original plugin. Development has been taken over by recursivetree from 13. February 2022. To find the applied changes, please consult the git history.

## Quick Installation:

See the instruction over at the [seat documentation](https://eveseat.github.io/docs/community_packages/).

The package name is `recursivetree/seat-billing`.

You should schedule a job running `billing:update` once at the beginning of a new month. 
It will finish the bill for the last month.

A second command `billing:update:live` should be added to the schedule automatically. 
It updates the data for the current month once a day. 
If you just installed the plugin and don't see any data, you can try to run it manually.

## Known issues
* Characters changing corporations causes all kind of issues.

Good luck, and Happy Hunting!!  o7

