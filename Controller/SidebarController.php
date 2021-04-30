<?php
/**
 * SidebarController.php
 * avanzu-admin
 * Date: 23.02.14
 */

namespace Avanzu\AdminThemeBundle\Controller;

use Avanzu\AdminThemeBundle\Event\ShowUserEvent;
use Avanzu\AdminThemeBundle\Event\SidebarMenuEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SidebarController extends Controller
{
    /**
     * Block used in macro avanzu_sidebar_user
     *
     * @return \Symfony\Component\HttpFoundation\Response|unknown
     */
    public function userPanelAction()
    {
        if (!$this->getDispatcher()->hasListeners(ShowUserEvent::class)) {
            return new Response();
        }
        $userEvent = $this->getDispatcher()->dispatch(new ShowUserEvent());

        return $this->render(
            '@AvanzuAdminTheme/Sidebar/user-panel.html.twig',
            [
                'user' => $userEvent->getUser(),
            ]
        );
    }

    /**
     * @return EventDispatcher
     */
    protected function getDispatcher()
    {
        return $this->get('event_dispatcher');
    }

    /**
     * Block used in macro avanzu_sidebar_search
     *
     * @return unknown
     */
    public function searchFormAction()
    {
        return $this->render('@AvanzuAdminTheme/Sidebar/search-form.html.twig', []);
    }

    public function menuAction(Request $request)
    {
        if (!$this->getDispatcher()->hasListeners(SidebarMenuEvent::class)) {
            return new Response();
        }

        $event = $this->getDispatcher()->dispatch(new SidebarMenuEvent($request));

        return $this->render(
            '@AvanzuAdminTheme/Sidebar/menu.html.twig',
            [
                'menu' => $event->getItems(),
            ]
        );
    }
}
