<?php

// log out the user
session_start();
session_destroy();

header('Location: /');