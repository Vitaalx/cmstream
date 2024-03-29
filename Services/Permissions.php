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

    /**
     * @return array<string>
     * 
     * This function returns all the permissions.
     */
    public static function getAllPermissions(): array
    {
        return [
            Permissions::AccessDashboard->value,
            Permissions::RoleEditor->value,
            Permissions::CommentsManager->value,
            Permissions::ContentsManager->value,
            Permissions::StatsViewer->value,
            Permissions::UserEditor->value,
            Permissions::ConfigEditor->value
        ];
    }
    /**
     * @param string $name
     * @return boolean
     * 
     * This function checks if the permission exists.
     */
    public static function exist(string $name): bool
    {
        return in_array($name, Permissions::getAllPermissions());
    }

    /**
     * @param string $name
     * @return Permissions|null
     * 
     * This function returns the permission by its name.
     */
    public static function getPermissionByName(string $name): ?Permissions
    {
        return match ($name) {
            Permissions::AccessDashboard->value => Permissions::AccessDashboard,
            Permissions::RoleEditor->value => Permissions::RoleEditor,
            Permissions::CommentsManager->value => Permissions::CommentsManager,
            Permissions::ContentsManager->value => Permissions::ContentsManager,
            Permissions::StatsViewer->value => Permissions::StatsViewer,
            Permissions::UserEditor->value => Permissions::UserEditor,
            Permissions::ConfigEditor->value => Permissions::ConfigEditor,
            default => null
        };
    }
}