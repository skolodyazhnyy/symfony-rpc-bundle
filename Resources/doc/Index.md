Symfony RPC Bundle
==================

Intro
-------------

This is a lightweight implementation of Remote Procedure Call (RPC) library for Symfony.
It provide an easy way to create a XML-RPC web service within standard Symfony controller.

Basic usage
-------------

It's really easy to create any kind of XML-RPC web services using RPC Bundle. First, you have to create
a regular Symfony controller and define an action which will be used to handle RPC requests.
You need to assign an URL to this action using standard Symfony routing configuration. This URL will be
used as your web service address.

