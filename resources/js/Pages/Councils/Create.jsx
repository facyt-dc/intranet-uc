import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link } from "@inertiajs/react";
import React from "react";

import ArrowCircleLeftRoundedIcon from "@mui/icons-material/ArrowCircleLeftRounded";
import IconButton from "@mui/material/IconButton";
import Tooltip from "@mui/material/Tooltip";

// Importamos el nuevo formulario de consejos
import Form from "./Components/Form";
import { useTranslation } from "react-i18next";

// Recibe la prop 'counselors' desde el CouncilController
export default function CouncilCreate({ auth, counselors }) {
    const { t } = useTranslation(["common"]);
    
    return (
        <AdminLayout auth={auth}>
            <Head
                title={t("button.create field", {
                    field: t("council", { count: 1 }),
                })}
            />
            <div className="flex justify-between items-center">
                <h2 className="text-xl text-gray-500">
                    {t("button.create field", {
                        field: t("council", { count: 1 }),
                    })}
                </h2>
                <Link href={route("councils.index")}>
                    <Tooltip title={t("button.go back")}>
                        <IconButton size="large">
                            <ArrowCircleLeftRoundedIcon fontSize="inherit" />
                        </IconButton>
                    </Tooltip>
                </Link>
            </div>

            {/* Renderizamos el formulario de consejos */}
            <Form
                counselors={counselors} // Pasamos la lista de consejeros
                method="post"
                routeName="councils.store"
            />
        </AdminLayout>
    );
}