<?php

namespace Draw\DrawBundle\Serializer;

class GroupHierarchy
{
    private $hierarchy;
    private $map;

    /**
     * Constructor.
     *
     * @param array $hierarchy An array defining the hierarchy
     */
    public function __construct(array $hierarchy)
    {
        $this->hierarchy = $hierarchy;

        $this->buildGroupMap();
    }

    /**
     * Return the list of groups this does refer to
     *
     * @return string[]
     */
    public function getReachableGroups(array $groups)
    {
        $reachableGroups = $groups;
        foreach ($groups as $group) {
            if (!isset($this->map[$group])) {
                continue;
            }

            $reachableGroups = array_merge($reachableGroups, $this->map[$group]);
        }

        return $reachableGroups;
    }

    private function buildGroupMap()
    {
        $this->map = array();
        foreach ($this->hierarchy as $main => $roles) {
            $this->map[$main] = $roles;
            $visited = array();
            $additionalRoles = $roles;
            while ($role = array_shift($additionalRoles)) {
                if (!isset($this->hierarchy[$role])) {
                    continue;
                }

                $visited[] = $role;
                $this->map[$main] = array_unique(array_merge($this->map[$main], $this->hierarchy[$role]));
                $additionalRoles = array_merge($additionalRoles, array_diff($this->hierarchy[$role], $visited));
            }
        }
    }
}