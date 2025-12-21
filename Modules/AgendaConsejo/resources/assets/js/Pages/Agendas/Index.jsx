import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, usePage } from "@inertiajs/react";
import React from "react";

import Button from "@mui/material/Button";
import AddRoundedIcon from "@mui/icons-material/AddRounded";

import Alert from "@/Components/Alert";

// Importamos el nuevo componente de tabla para Consejos
import AgendaTable from "./Components/AgendaTable"; 

import { useTranslation } from "react-i18next";
import { useEffect } from "react";

// Renombramos la prop 'roles' a 'agendas'
export default function AgendaIndex({ auth, agendas}) {
    const { flash } = usePage().props;
    const alert = flash?.alert;
    const { t } = useTranslation(["common", "agenda"]);
    const isDirector = auth.user.roles.some(role => role.name === 'director');

    return (
        <AdminLayout auth={auth}>
            {/* Título de la página: "Agendas" */}
            <Head title={t("agenda:Agenda", { count: 2 })} />
            
            {/* Mostrar alertas de éxito o error */}
            {alert && (
                <Alert
                    key={alert.id}
                    message={alert.message}
                    severity={alert.severity}
                />
            )}

            <div className="flex justify-between items-center">
                <h2 className="text-xl text-gray-500 capitalize">
                    {/* Título de la lista: "List of Agendas" */}
                    {t("list of field", { field: t("agenda:Agendas", { count: 2 }) })}
                </h2>
                
                {/* Ocultamos el botón "Crear" si no es Director */}
                {isDirector && (
                    <Link href={route("agendas.create")}>
                        <Button variant="contained" startIcon={<AddRoundedIcon />}>
                            {t("button.create field", {
                                field: t("agenda:Agenda", { count: 1 }),
                            })}
                        </Button>
                    </Link>
                )}
            </div>

            {/* Pasamos los datos paginados a la tabla */}
            <AgendaTable agendas={agendas} />
        </AdminLayout>
    );
}