<?php

class BookingPluginDeactivate
{
  public static function deactivate() {
    flush_rewrite_rules();
  }
}
