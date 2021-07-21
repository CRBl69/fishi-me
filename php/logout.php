<?php

// Clearing session
session_start();
session_unset();
header('Location: /login');