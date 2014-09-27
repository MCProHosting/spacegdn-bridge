<?php

namespace Mcprohosting\Spacegdn;

use GuzzleHttp\Client;

class Bridge implements \Countable, \IteratorAggregate
{
    /**
     * Guzzle instance.
     *
     * @var Client
     */
    protected $guzzle;

    /**
     * URL endpoint for the GDN.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Route parts to request.
     *
     * @var array
     */
    protected $route;

    /**
     * Results of the query.
     *
     * @var \stdClass
     */
    protected $results;

    /**
     * List of all request parameters to be converted into results.
     *
     * @var array
     */
    protected $parameters;

    public function __construct(Client $guzzle = null)
    {
        $this->guzzle = $guzzle ?: new Client;
        $this->clear();
    }

    /**
     * Sets the GDN URL to use.
     *
     * @param string $endpoint
     * @return self
     */
    public function setEndpoint($endpoint)
    {
        if (strpos($endpoint, '//') === false) {
            $endpoint = 'http://' . $endpoint;
        }

        $this->endpoint = rtrim($endpoint, '/') . '/';

        return $this;
    }

    /**
     * Resets parameters.
     *
     * @return array
     */
    public function clear()
    {
        $this->parameters = [];
        $this->route = ['v2'];
        $this->results = null;

        return $this;
    }

    /**
     * Sets the parents of the record we're looking for.
     *
     * @param string|[]string $ids
     * @return self
     */
    public function belongingTo($ids)
    {
        if (!is_array($ids)) {
            return $this->belongingTo([$ids]);
        }

        foreach ($ids as $id) {
            $this->route[] = $id;
        }

        return $this;
    }

    /**
     * Sets that we would like to include the result parents in the query.
     *
     * @return self
     */
    public function withParents()
    {
        $this->parameters['parents'] = true;

        return $this;
    }

    /**
     * Sets the resource to get.
     *
     * @param string $item
     * @return self
     */
    public function get($item = '')
    {
        $this->parameters['r'] = rtrim($item, 's');

        return $this;
    }

    /**
     * Adds a "where" query to the request.
     *
     * @param string $column
     * @param string $operator
     * @param string|array $value
     * @return self
     */
    public function where($column, $operator, $value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        $this->parameters['where'] = implode('.', [$column, $operator, $value]);

        return $this;
    }

    /**
     * Sets the page to get.
     *
     * @param integer $page
     * @return self
     */
    public function page($page)
    {
        $this->parameters['page'] = $page;

        return $this;
    }

    /**
     * Sets the order for the request.
     *
     * @param string $column
     * @param string $direction One of "asc", "desc"
     * @return self
     */
    public function orderBy($column, $direction)
    {
        $this->parameters['sort'] = $column . '.' . $direction;

        return $this;
    }

    /**
     * Gets the results of the query, executing the query if it's not yet been done.
     *
     * @return \stdClass
     */
    public function results()
    {
        if (!$this->results) {
            $response = $this->guzzle->get($this->buildUrl());

            $this->results = $response->json();
        }

        return $this->results;
    }

    /**
     * Builds a request URL
     *
     * @return string
     */
    public function buildUrl()
    {
        $base = rtrim($this->endpoint . implode('/', $this->route), '/') . '/?';

        foreach ($this->parameters as $key => $value) {
            $base .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        return $base . 'json';
    }

    public function count()
    {
        return count($this->toArray());
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->toArray());
    }

    /**
     * Returns the results as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->results()['results'];
    }

    /**
     * Returns the results as a JSON string.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Gets a property of the results.
     *
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        return $this->results()[$attribute];
    }
} 
