import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, usePage } from "@inertiajs/react";
import React from "react";

import Button from "@mui/material/Button";
import AddRoundedIcon from "@mui/icons-material/AddRounded";

import Alert from "@/Components/Alert";

// Importamos el nuevo componente de tabla para Consejos
import CouncilTable from "./Components/CouncilTable"; 

import { useTranslation } from "react-i18next";

// Renombramos la prop 'roles' a 'councils'
export default function CouncilIndex({ auth, councils, flash }) {
    const alert = flash?.alert;

    // Asumimos que tienes traducciones para 'council' en tu archivo común.
    const { t } = useTranslation(["common"]);

    return (
        <AdminLayout auth={auth}>
            {/* Título de la página: "Councils" */}
            <Head title={t("council", { count: 2 })} />
            
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
                    {/* Título de la lista: "List of Councils" */}
                    {t("list of field", { field: t("council", { count: 2 }) })}
                </h2>
                
                {/* Botón para crear un nuevo consejo */}
                <Link href={route("councils.create")}>
                    <Button variant="contained" startIcon={<AddRoundedIcon />}>
                        {t("button.create field", {
                            field: t("council", { count: 1 }),
                        })}
                    </Button>
                </Link>
            </div>

            {/* Pasamos los datos paginados a la tabla */}
            <CouncilTable councils={councils} />
        </AdminLayout>
    );
}