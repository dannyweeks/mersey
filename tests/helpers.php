<?php

function loadFixture($type, $fileName)
{
    return sprintf('%s/fixtures/%s/%s', __DIR__, $type, $fileName);
}