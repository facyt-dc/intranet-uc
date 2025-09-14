import React, { useState, useEffect, Fragment } from "react";
import { DragDropContext, Droppable, Draggable } from "react-beautiful-dnd";
import { router } from '@inertiajs/react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Link } from "@inertiajs/react";
import AdvancedFilterMenu from "@/Components/AdvancedFilterMenu";

const ArchiveBoxIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-5 h-5">
      <path d="M2 3.5A1.5 1.5 0 0 1 3.5 2h13A1.5 1.5 0 0 1 18 3.5v2.753a1.5 1.5 0 0 1-.812 1.342l-1.558.623 1.558.623A1.5 1.5 0 0 1 18 10.247V13.5A1.5 1.5 0 0 1 16.5 15h-13A1.5 1.5 0 0 1 2 13.5v-3.253a1.5 1.5 0 0 1 .812-1.342l1.558-.623-1.558-.623A1.5 1.5 0 0 1 2 6.253V3.5ZM3.5 3.5h13V6a.75.75 0 0 1-.406.67l-6.594 2.637a.75.75 0 0 1-.599 0L3.906 6.67A.75.75 0 0 1 3.5 6V3.5ZM16.5 11h-13v2.5h13V11Z" />
    </svg>
);

function RequestCard({ request, index }) {
    return (
        <Draggable draggableId={String(request.id)} index={index}>
            {(provided, snapshot) => (
                <Link
                    href={route("mantenimiento.show", request.id)}
                    ref={provided.innerRef}
                    {...provided.draggableProps}
                    {...provided.dragHandleProps}
                    className={`block p-3 mb-3 bg-white rounded-md shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors duration-150 ${
                        snapshot.isDragging ? "shadow-lg" : ""
                    }`}
                >
                    <h4 className="font-bold text-gray-800">{request.title}</h4>
                     {request.equipment?.name && (
                        <p className="flex items-center gap-2 text-sm text-gray-700 mt-2">
                            <svg fill="#000000" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-500"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M16.417 9.579A7.917 7.917 0 1 1 8.5 1.662a7.917 7.917 0 0 1 7.917 7.917zm-3.831-2.295a.396.396 0 0 0 .182-.53 4.697 4.697 0 0 0-4.225-2.642.4.4 0 0 0-.077.008 1.526 1.526 0 0 0-1.16.55h-.001l-.005.007a1.532 1.532 0 0 0-.096.131.846.846 0 0 1-1.327-.084.396.396 0 0 0-.772.123v1.605a.396.396 0 0 0 .768.137.846.846 0 0 1 1.35-.07q.028.042.06.08a.845.845 0 0 1 .137.464v.284h2.416v-.764a.846.846 0 0 1 .846-.846.838.838 0 0 1 .388.094 3.915 3.915 0 0 1 .987 1.27.396.396 0 0 0 .356.223.389.389 0 0 0 .173-.04zm-3.088.945a.318.318 0 0 0-.317-.316H7.938a.318.318 0 0 0-.317.316v6.687a.318.318 0 0 0 .317.317h1.243a.318.318 0 0 0 .317-.317z"></path></g></svg>
                           <span>{request.equipment.name}</span>
                        </p>
                    )}
                    <p className="text-sm text-gray-600 mt-1">
                        {request.description.substring(0, 80)}...
                    </p>
                    {request.user && (
                        <p className="text-xs text-gray-500 mt-2">
                            Reportado por: {request.user.name}
                        </p>
                    )}
                </Link>
            )}
        </Draggable>
    );
}

export default function KanbanBoard({ auth, initialStages, initialRequests, technicians, equipments, equipmentCategories, filters }) {
    const [columns, setColumns] = useState({});

    const [filterValues, setFilterValues] = useState({
        search: filters.search || '',
        technician: filters.technician || '',
        equipment: filters.equipment || '',
        category: filters.category || '',
    });

    // --- Lógica de filtrado (sin cambios) ---
    useEffect(() => {
        const handler = setTimeout(() => {
            router.get(route('mantenimiento.index'), filterValues, {
                preserveState: true,
                replace: true,
            });
        }, 300);
        return () => clearTimeout(handler);
    }, [filterValues]);

    const handleSearchChange = (e) => {
        setFilterValues(prev => ({ ...prev, search: e.target.value }));
    };
    
    const handleFilterChange = (name, value) => {
        setFilterValues(prev => ({ ...prev, [name]: value }));
    };
    
    const resetFilters = () => {
        setFilterValues({ search: '', technician: '', equipment: '', category: '' });
    };

    useEffect(() => {
        const organizedColumns = initialStages.reduce((acc, stage) => {
            acc[stage.id] = initialRequests.filter(
                (req) => req.stage_id === stage.id
            ) || [];
            return acc;
        }, {});
        setColumns(organizedColumns);
    }, [initialStages, initialRequests]);

    const { search, ...dropdownFilters } = filterValues;
    const activeFilterCount = Object.values(dropdownFilters).filter(Boolean).length;

    const onDragEnd = ({ source, destination, draggableId }) => {
        if (!destination || (source.droppableId === destination.droppableId && source.index === destination.index)) {
            return;
        }

        const originalColumns = { ...columns };
        const sourceCol = Array.from(columns[source.droppableId]);
        const [movedItem] = sourceCol.splice(source.index, 1);
        const destCol = Array.from(columns[destination.droppableId] || []);
        destCol.splice(destination.index, 0, movedItem);

        setColumns({
            ...columns,
            [source.droppableId]: sourceCol,
            [destination.droppableId]: destCol,
        });

        router.post(route('mantenimiento.updateStage', {
            maintenanceRequest: draggableId
        }), {
            stage: destination.droppableId
        }, {
            preserveScroll: true,
            onError: () => setColumns(originalColumns),
        });
    };

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800">
                        Tablero de Mantenimiento
                    </h2>
                </div>
            }
        >
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5">
                    <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5">
                        <div className="flex items-center gap-4">
                            <Link
                                href={route("mantenimiento.create")}
                                className="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-500 transition whitespace-nowrap"
                            >
                                + Nueva Solicitud
                            </Link>
                            
                            {/* Input de Búsqueda (se mantiene fuera del menú) */}
                            <div className="relative flex-grow">
                                <input
                                    type="text"
                                    name="search"
                                    value={filterValues.search}
                                    onChange={handleSearchChange}
                                    placeholder="Buscar por título o descripción..."
                                    className="form-input rounded-md shadow-sm text-sm w-full md:w-80"
                                />
                            </div>

                            <div className="ml-auto">
                                <AdvancedFilterMenu
                                    filterValues={filterValues}
                                    onFilterChange={handleFilterChange}
                                    onResetFilters={resetFilters}
                                    technicians={technicians}
                                    equipments={equipments}
                                    equipmentCategories={equipmentCategories}
                                />
                            </div>
                            <Link
                                href={route('mantenimiento.archived.index')}
                                className="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 ring-1 ring-gray-300 rounded-md text-sm font-semibold hover:bg-gray-50 transition whitespace-nowrap"
                            >
                                <ArchiveBoxIcon />
                                Ver Archivados
                            </Link>
                        </div>
                    </div>
                </div>
                <div className="sm:px-6 lg:px-8">
                    <DragDropContext onDragEnd={onDragEnd}>

                        <div className="flex overflow-x-auto gap-5 pb-4">
                            {initialStages.map((stage) => (
                                <Droppable
                                    key={stage.id}
                                    droppableId={String(stage.id)}
                                >
                                    {(provided, snapshot) => (
                                        <div
                                            ref={provided.innerRef}
                                            {...provided.droppableProps}
                                            className={`w-80 flex-shrink-0 p-4 rounded-lg transition-colors ${
                                                snapshot.isDraggingOver
                                                    ? "bg-blue-100"
                                                    : "bg-gray-100"
                                            }`}
                                        >
                                            <h3 className="font-bold text-lg mb-4">
                                                {stage.name}
                                            </h3>
                                            {(columns[stage.id] || []).map(
                                                (request, index) => (
                                                    <RequestCard
                                                        key={request.id}
                                                        request={request}
                                                        index={index}
                                                    />
                                                )
                                            )}
                                            {provided.placeholder}
                                        </div>
                                    )}
                                </Droppable>
                            ))}
                        </div>
                    </DragDropContext>
                </div>
            </div>
        </AdminLayout>
    );
}