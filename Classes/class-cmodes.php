<?php

/**
 * A set of reference lists
 */
class IRCList {
    public static $grouping = 
    [
        "Join restrictions"=>"kliRzOL",
        "Message restrictions"=>"cSmMnGT",
        "Anti-flood and other restrictions"=>"FftCNKVQ",
        "Visibility"=>"sp",
        "Other"=>"rPHzZDd",
    ];
    public static $cmodes =
    [
        "a" =>  [
            "name" => "Admin",
            "description" => "Marks someone as channel admin (&)",
            "requires" => "Admin"
        ],
        "b" => [
            "name" => "Ban",
            "description" => "Marks a ban from a channel",
            "requires" => "HalfOp"
        ],
        "c" => [
            "name" => "No colors",
            "description" => "Block messages containing mIRC color codes",
            "requires" => "Operator"
        ],
        "d" => [
            "name" => "Delay Join",
            "description" => "Indicates there are invisible users left over due to unsetting 'D'",
            "requires" => "Server"
        ],
        "e" => [
            "name" => "Ban Exemption",
            "description" => "Marks an exemption from channel bans",
            "requires" => "HalfOp"
        ],
        "f" => [
            "name" => "Flood Protection",
            "description" => "Implements channel flood protection",
            "requires" => "Operator"
        ],
        "h" => [
            "name" => "Half Op",
            "description" => "Marks someone as channel halfop (%)",
            "requires" => "Operator"
        ],
        "i" => [
            "name" => "Invite Only",
            "description" => "Requires an invitation to join",
            "requires" => "HalfOp"
        ],
        "k" => [
            "name" => "Key",
            "description" => "Requires a key/password to join",
            "requires" => "HalfOp"
        ],
        "l" => [
            "name" => "Limit",
            "description" => "Limits a channel to a specific amount of users",
            "requires" => "HalfOp"
        ],
        "m" => [
            "name" => "Moderation",
            "description" => "Prevents non-voiced users from speaking in a channel",
            "requires" => "HalfOp"
        ],
        "n" => [
            "name" => "No External Messages",
            "description" => "Messages cannot be sent to the channel from outside it",
            "requires" => "HalfOp"
        ],
        "o" => [
            "name" => "Operator",
            "description" => "Marks someone as channel operator (@)",
            "requires" => "Operator"
        ],
        "p" => [
            "name" => "Private",
            "description" => "Prevents the channel from showing up in <code>/WHOIS</code> outputs and is replaces with \"*\" in <code>/LIST</code> outputs",
            "requires" => "Operator"
        ],
        "q" => [
            "name" => "Owner",
            "description" => "Marks someone as channel owner (~)",
            "requires" => "Owner"
        ],
        "r" => [
            "name" => "Registered",
            "description" => "Channel has been registered to an account",
            "requires" => "Server"
        ],
        "s" => [
            "name" => "Secret",
            "description" => "Prevents the channel from showing up in <code>/WHOIS</code> and <code>/LIST</code> outputs",
            "requires" => "Operator"
        ],
        "t" => [
            "name" => "Topic",
            "description" => "Only HalfOps and above may set the topic.",
            "requires" => "HalfOp"
        ],
        "v" => [
            "name" => "Voice",
            "description" => "Marks someone as voiced in the channel (+)",
            "requires" => "HalfOp"
        ],
        "z" => [
            "name" => "Secure Only",
            "description" => "Only users using a secure connection may join this channel.",
            "requires" => "Operator"
        ],
        "C" => [
            "name" => "No CTCPs",
            "description" => "CTCP messages are not allowed on the channel.",
            "requires" => "Operator"
        ],
        "D" => [
            "name" => "Delay Join",
            "description" => "Delay showing joins until someone actually speaks.",
            "requires" => "Operator"
        ],
        "F" => [
            "name" => "Flood Profile",
            "description" => "Uses a Flood Profile to easily apply flood protection mechanisms",
            "requires" => "Operator"
        ],
        "G" => [
            "name" => "Filter",
            "description" => "Filters out all Bad words in messages with \"&lt;censored&gt;</pre>\".",
            "requires" => "Operator"
        ],
        "H" => [
            "name" => "History",
            "description" => "Record channel history with specified maximums.",
            "requires" => "Operator"
        ],
        "I" => [
            "name" => "Invitation",
            "description" => "Marks an inviation to a channel.",
            "requires" => "HalfOp"
        ],
        "K" => [
            "name" => "No Knock",
            "description" => "Users may not knock on this channel.",
            "requires" => "HalfOp"
        ],
        "L" => [
            "name" => "Link",
            "description" => "Link to another channel when unable to join",
            "requires" => "Operator"
        ],
        "M" => [
            "name" => "Auth Moderated",
            "description" => "Only users who have voice or are authenticated may talk in this channel.",
            "requires" => "HalfOp"
        ],
        "N" => [
            "name" => "No Nick Changes",
            "description" => "Nickname changes are not permitted on the channel.",
            "requires" => "HalfOp"
        ],
        "O" => [
            "name" => "IRCOps Only",
            "description" => "Only IRC Operators may join this channel.",
            "requires" => "IRC Operator"
        ],
        "P" => [
            "name" => "Permanent",
            "description" => "This channel will exist even when nobody is inside.",
            "requires" => "IRC Operator"
        ],
        "Q" => [
            "name" => "No Kicks",
            "description" => "Kicks are not allowed in this channel.",
            "requires" => "Operator"
        ],
        "R" => [
            "name" => "Reg Only",
            "description" => "Only registered/authenticated users may join the channel.",
            "requires" => "Operator"
        ],
        "S" => [
            "name" => "Strip Color",
            "description" => "All color is stripped from channel messages.",
            "requires" => "IRC Operator"
        ],
        "T" => [
            "name" => "No Notices",
            "description" => "Notices are not permitted on the channel.",
            "requires" => "IRC Operator"
        ],
        "V" => [
            "name" => "No Invites",
            "description" => "Users are not allowed to <code>/INVITE</code> others to the channel.",
            "requires" => "IRC Operator"
        ],
        "Z" => [
            "name" => "Is Secure",
            "description" => "Indication that all users on the channel are on a Secure connection.",
            "requires" => "Server"
        ]
    ];

    static function lookup($mode)
    {
        return (isset(self::$cmodes[$mode])) ? self::$cmodes[$mode] :
        [
            'name' => "Unknown mode",
            'description' => "Unknown mode +$mode",
            'requires' => 'Unknown'
        ];
    }
    static function setmodes($modes)
    {
        $g = [];
        if (is_array($modes))
        {
            self::$uplink = $modes;
            return;
        }
        else if (!strstr($modes,","))
            $g = [$modes];
        else $g = split($g,",");
        self::$uplink = $g;
    }
    static $uplink = [];
    
}
