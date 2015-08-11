<?php

namespace Draw\DrawBundle\Serializer;

class GroupHierarchy
{
    private $hierarchy;
    private $groupAlwaysPresent;
    private $map;

    /**
     * Constructor.
     *
     * @param array $hierarchy An array defining the hierarchy
     */
    public function __construct(array $hierarchy, array $groupsAlwaysPresent)
    {
        $this->hierarchy = $hierarchy;
        $this->groupAlwaysPresent = $groupsAlwaysPresent;

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

        return array_unique(
            array_merge($reachableGroups, $this->groupAlwaysPresent)
        );
    }

    private function buildGroupMap()
    {
        $this->map = array();
        foreach ($this->hierarchy as $main => $groups) {
            $this->map[$main] = $groups;
            $visited = array();
            $additionalGroups = $groups;
            while ($group = array_shift($additionalGroups)) {
                if (!isset($this->hierarchy[$group])) {
                    continue;
                }

                $visited[] = $group;
                $this->map[$main] = array_unique(array_merge($this->map[$main], $this->hierarchy[$group]));
                $additionalGroups = array_merge($additionalGroups, array_diff($this->hierarchy[$group], $visited));
            }
        }
    }
}