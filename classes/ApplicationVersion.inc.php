<?php
class ApplicationVersion
{   
    public static function get()
    {
        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));

        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));
		$branch = trim(exec('git branch --list'));		
        return sprintf('%s %s (%s)', $branch, $commitHash, $commitDate->format('Y-m-d H:m:s'));
    }
}
?>