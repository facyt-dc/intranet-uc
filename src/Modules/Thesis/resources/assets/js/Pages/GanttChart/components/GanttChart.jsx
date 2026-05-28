// src/Components/GanttChart.jsx

import React from 'react';
import {
    GanttProvider,
    GanttSidebar,
    GanttSidebarGroup,
    GanttSidebarItem,
    GanttTimeline,
    GanttHeader,
    GanttFeatureList,
    GanttFeatureListGroup,
    GanttFeatureItem,
} from '@/Components/ui/shadcn-io/gantt';
import groupBy from 'lodash.groupby';

// Mapeo de colores (esto está perfecto)
const statusColorMap = {
    'pteg inscrito': '#3b82f6',
    'inscrito': '#22c55e',
    'teg inscrito': '#f59e0b',
};
const defaultColor = '#64748b';

export default function GanttChart({ data }) {
    // La transformación de datos está bien
    const features = data.flatMap(student =>
        student.statuses.map((status, index) => ({
            id: `${student.student_id}-${index}`,
            startAt: new Date(status.start),
            endAt: new Date(status.end),
            name: status.status_name,
            group: { name: student.student_name },
            status: {
                name: status.status_name,
                color: statusColorMap[status.status_name.toLowerCase()] || defaultColor
            },
        }))
    );

    const groupedFeatures = groupBy(features, 'group.name');
    const sortedGroupedFeatures = Object.fromEntries(
        Object.entries(groupedFeatures).sort(([nameA], [nameB]) =>
            nameA.localeCompare(nameB)
        )
    );

    const handleViewItem = (id) => console.log(`Item seleccionado: ${id}`);
    const handleMoveItem = (id, startAt, endAt) => {
        if (!endAt) return;
        console.log(`Mover item: ${id} de ${startAt} a ${endAt}`);
    };

    return (
        <GanttProvider
            className="border rounded-lg"
            range="monthly"
            zoom={100}
            features={features}
        >
            <GanttSidebar>
                {/* Esta estructura es la correcta y la que queremos replicar */}
                {Object.entries(sortedGroupedFeatures).map(([studentName, statuses]) => (
                    <GanttSidebarGroup key={studentName} name={studentName}>
                        {statuses.map((status) => (
                            <GanttSidebarItem
                                feature={status}
                                key={status.id}
                                onSelectItem={handleViewItem}
                            />
                        ))}
                    </GanttSidebarGroup>
                ))}
            </GanttSidebar>
            <GanttTimeline>
                <GanttHeader />
                <GanttFeatureList>
                    {Object.entries(sortedGroupedFeatures).map(([studentName, statuses]) => (
                        <GanttFeatureListGroup key={studentName}>
                          
                            {statuses.map((status) => (
                                <GanttFeatureItem
                                    key={status.id}
                                    {...status} 
                                    onMove={handleMoveItem}
                                    style={{ backgroundColor: status.status.color }}
                                >
                                    <p className="flex-1 truncate text-xs font-medium text-white px-2">
                                        {status.name}
                                    </p>
                                </GanttFeatureItem>
                            ))}

                        </GanttFeatureListGroup>
                    ))}
                </GanttFeatureList>
            </GanttTimeline>
        </GanttProvider>
    );
};