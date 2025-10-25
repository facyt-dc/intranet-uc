import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link } from "@inertiajs/react";
import React from "react";

// --- Material-UI Components ---
import ArrowCircleLeftRoundedIcon from "@mui/icons-material/ArrowCircleLeftRounded";
import IconButton from "@mui/material/IconButton";
import Tooltip from "@mui/material/Tooltip";

// --- Componentes y Traducciones ---
// Reutilizamos el mismo formulario que en la página de creación
import Form from "./Components/Form"; 
import { useTranslation } from "react-i18next";

/**
 * Página para editar un consejo existente.
 * 
 * @param {object} auth - El objeto de autenticación del usuario.
 * @param {object} agenda - El objeto del consejo a editar. Incluye los participantes cargados.
 * @param {array} counselors - La lista completa de todos los usuarios con el rol 'Consejero'.
 */
export default function AgendaEdit({ auth, agenda, counselors }) {
    const { t } = useTranslation(["translation", "common"]);
    
    return (
        <AdminLayout auth={auth}>
            <Head
                title={t("Edit resource", {
                    resource: t("agenda", {
                        count: 1,
                        ns: "common",
                    }),
                })}
            />
            <div className="flex justify-between items-center">
                <h2 className="text-xl text-gray-500">
                    {/* Título: "Editar Consejo" */}
                    {t("Edit resource", {
                        resource: t("agenda", {
                            count: 1,
                            ns: "common",
                        }),
                    })}
                </h2>
                
                {/* Botón para volver a la lista de consejos */}
                <Link href={route("agendas.index")}>
                    <Tooltip title={t("button.go back", { ns: "common" })}>
                        <IconButton size="large">
                            <ArrowCircleLeftRoundedIcon fontSize="inherit" />
                        </IconButton>
                    </Tooltip>
                </Link>
            </div>

            {/* 
              Renderizamos el mismo componente Form que en Create.jsx.
              La diferencia clave es que le pasamos las props 'agenda',
              le indicamos que el método es 'patch' y la ruta es 'agendas.update'.
            */}
            <Form
                agenda={agenda} // Los datos del consejo para pre-rellenar el formulario
                counselors={counselors} // La lista completa de consejeros para el selector
                method="patch" // El método HTTP para la actualización
                routeName="agendas.update" // El nombre de la ruta de actualización
            />
        </AdminLayout>
    );
}