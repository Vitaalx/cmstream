<?php

namespace Services;

enum Permissions : string
{
    case AccessDashboard = "access_dashboard";
    case RoleEditor = "role_editor";
    case CommentsManager = "comments_manager";
    case ContentsManager = "contents_manager";
    case StatsViewer = "stats_viewer";
    case UserEditor = "user_editor";
    case ConfigEditor = "config_editor";
}