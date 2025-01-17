<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Mapping\Killmails;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class AttackerMapping.
 *
 * @package Seat\Eveapi\Mapping\Killmails
 */
class AttackerMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'character_id'    => 'character_id',
        'corporation_id'  => 'corporation_id',
        'alliance_id'     => 'alliance_id',
        'faction_id'      => 'faction_id',
        'security_status' => 'security_status',
        'final_blow'      => 'final_blow',
        'damage_done'     => 'damage_done',
        'ship_type_id'    => 'ship_type_id',
        'weapon_type_id'  => 'weapon_type_id',
    ];
}
