import React, { useState, useEffect } from "react";
import { DragDropContext, Droppable, Draggable } from "react-beautiful-dnd";
import { router } from '@inertiajs/react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Link } from "@inertiajs/react";

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

export default function KanbanBoard({ auth, initialStages, initialRequests }) {
    const [columns, setColumns] = useState({});

    // Organiza las solicitudes en columnas al iniciar
    useEffect(() => {
        const organizedColumns = initialStages.reduce((acc, stage) => {
            // Se asegura de que cada columna sea un array, incluso si no hay solicitudes
            acc[stage.id] = initialRequests.filter(
                (req) => req.stage_id === stage.id
            ) || [];
            return acc;
        }, {});
        setColumns(organizedColumns);
    }, [initialStages, initialRequests]);

    /**
     * Maneja el final del arrastre de un elemento.
     * Actualiza el estado local de forma optimista y envía la actualización al servidor.
     */
    const onDragEnd = ({ source, destination, draggableId }) => {
        // Si se suelta fuera de una columna, no hacer nada
        if (!destination) return;

        // Si se suelta en la misma posición, no hacer nada
        if (
            source.droppableId === destination.droppableId &&
            source.index === destination.index
        ) {
            return;
        }

        // --- Actualización Optimista de la UI ---
        // Guardamos el estado original para poder revertirlo en caso de error
        const originalColumns = { ...columns };

        // Mover el elemento en el estado local
        const sourceCol = Array.from(columns[source.droppableId]);
        const [movedItem] = sourceCol.splice(source.index, 1);

        const destCol = Array.from(columns[destination.droppableId] || []);
        destCol.splice(destination.index, 0, movedItem);

        setColumns({
            ...columns,
            [source.droppableId]: sourceCol,
            [destination.droppableId]: destCol,
        });

        // --- Petición al Servidor con Inertia ---
        // Usamos router.post para actualizar el estado en el backend.
        router.post(
            route('mantenimiento.updateStage', {
                maintenanceRequest: draggableId,
                stage: destination.droppableId
            }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    // La UI ya se actualizó. Opcionalmente puedes mostrar una notificación de éxito.
                    console.log("Etapa actualizada correctamente.");
                },
                onError: (errors) => {
                    console.error("Error al actualizar la etapa:", errors);
                    setColumns(originalColumns);
                },
            }
        );
    };

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800">
                        Mantenimiento
                    </h2>
                </div>
            }
        >
            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5">
                     <Link
                        href={route("mantenimiento.create")}
                        className="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-500 transition"
                    >
                        + Nueva Solicitud
                    </Link>
                </div>
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <DragDropContext onDragEnd={onDragEnd}>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                            {initialStages.map((stage) => (
                                <Droppable
                                    key={stage.id}
                                    droppableId={String(stage.id)}
                                >
                                    {(provided, snapshot) => (
                                        <div
                                            ref={provided.innerRef}
                                            {...provided.droppableProps}
                                            className={`p-4 rounded-lg transition-colors ${
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
