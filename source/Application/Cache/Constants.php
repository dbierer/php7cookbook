<?php
namespace Application\Cache;

class Constants
{
	const DEFAULT_GROUP      = 'default';
    const DEFAULT_PREFIX     = 'CACHE_';
    const DEFAULT_SUFFIX     = '.cache';
	const ERROR_GET          = 'ERROR: unable to retrieve from cache';
    const ERROR_SAVE         = 'ERROR: unable to write to cache';
    const ERROR_REMOVE_KEY   = 'ERROR: unable to remove by key';
	const ERROR_DB_PREPARE   = 'ERROR: unable to prepare statements';
    const ERROR_REMOVE_GROUP = 'ERROR: unable to remove by group';
    const ERROR_DIR_NOT      = 'ERROR: cache directory not found';
}
